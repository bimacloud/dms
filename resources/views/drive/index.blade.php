@extends('layouts.app')

@section('header', 'My Drive')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('driveData', () => ({
        showNewFolderModal: false,
        folderToRename: null,
        folderName: '',
        showMoveModal: false,
        moveType: 'document',
        moveId: null,
        moveTargetUrl: '',
        showDeleteModal: false,
        deleteFormAction: '',
        deleteTitle: '',
        deleteType: '',
        showShareModal: false,
        shareModalDocId: '',
        shareModalDocTitle: '',
        showPreviewModal: false,
        previewUrl: '',
        previewName: '',
        contextMenuOpen: false,
        contextMenuX: 0,
        contextMenuY: 0,
        contextMenuType: '', // 'bg', 'folder', 'file'
        contextMenuFolder: null, // {id, name}
        contextMenuFile: null, // {id, name, type}
        isDragging: false,
        isUploading: false,
        uploadProgress: 0,
        draggedType: null,
        draggedId: null,
        dragHoverFolder: null,

        showContextMenu(e, type, item = null) {
            this.contextMenuType = type;
            if (type === 'folder') this.contextMenuFolder = item;
            if (type === 'file') this.contextMenuFile = item;
            
            this.contextMenuX = e.clientX;
            this.contextMenuY = e.clientY;
            
            this.$nextTick(() => {
                const menu = this.$refs.ctxMenu;
                if(menu) {
                    const rect = menu.getBoundingClientRect();
                    if(this.contextMenuX + rect.width > window.innerWidth) this.contextMenuX -= rect.width;
                    if(this.contextMenuY + rect.height > window.innerHeight) this.contextMenuY -= rect.height;
                }
            });
            
            this.contextMenuOpen = true;
        },
        openShareModal(id, title) {
            this.shareModalDocId = id;
            this.shareModalDocTitle = title;
            this.showShareModal = true;
        },
        openDeleteModal(type, title, actionTarget) {
            this.deleteType = type;
            this.deleteTitle = title;
            this.deleteFormAction = actionTarget;
            this.showDeleteModal = true;
        },
        openRenameModal(folderId, name) {
            this.folderToRename = folderId;
            this.folderName = name;
            this.showNewFolderModal = false;
        },
        openMoveModal(type, id) {
            this.moveType = type;
            this.moveId = id;
            this.moveTargetUrl = type === 'folder' 
                ? '{{ url('folders') }}/' + id
                : '{{ url('documents') }}/' + id;
            this.showMoveModal = true;
        },
        openPreviewModal(url, name) {
            this.previewUrl = url;
            this.previewName = name;
            this.showPreviewModal = true;
        },
        checkDragOver(e) {
            if (e.dataTransfer.types && e.dataTransfer.types.includes('Files') && !this.draggedType) {
                this.isDragging = true;
            }
        },
        startDrag(type, id, e) {
            this.draggedType = type;
            this.draggedId = id;
            e.dataTransfer.effectAllowed = 'move';
        },
        handleInternalDrop(targetFolderId, e) {
            if (!this.draggedType) return;
            e.stopPropagation();
            
            if (this.draggedType === 'folder' && this.draggedId == targetFolderId) {
                this.dragHoverFolder = null;
                this.draggedType = null;
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.draggedType === 'folder' 
                ? '{{ url('folders') }}/' + this.draggedId
                : '{{ url('documents') }}/' + this.draggedId;
            
            form.innerHTML = `
                <input type='hidden' name='_token' value='{{ csrf_token() }}'>
                <input type='hidden' name='_method' value='PUT'>
                <input type='hidden' name='${this.draggedType === 'folder' ? 'parent_id' : 'folder_id'}' value='${targetFolderId || ''}'>
            `;
            
            document.body.appendChild(form);
            form.submit();
            
            this.dragHoverFolder = null;
            this.draggedType = null;
        },
        handleDrop(e) {
            if (this.draggedType) return;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFiles(files);
            }
        },
        async uploadFiles(files) {
            this.isUploading = true;
            this.uploadProgress = 0;
            
            let totalSize = Array.from(files).reduce((acc, f) => acc + f.size, 0);
            let loadedSizes = new Array(files.length).fill(0);
            let hasError = false;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                try {
                    const startRes = await fetch('{{ route('drive.upload') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            filename: file.name,
                            folder_id: '{{ $currentFolder ? $currentFolder->id : '' }}'
                        })
                    });
                    
                    if (!startRes.ok) throw new Error('Failed');
                    const uploadData = await startRes.json();

                    const xhr = new XMLHttpRequest();
                    xhr.open('PUT', uploadData.upload_url, true);
                    
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            loadedSizes[i] = e.loaded;
                            let currentTotalLoaded = loadedSizes.reduce((a, b) => a + b, 0);
                            this.uploadProgress = Math.round((currentTotalLoaded / totalSize) * 100);
                        }
                    };

                    const uploadPromise = new Promise((res, rej) => {
                        xhr.onload = () => xhr.status >= 200 && xhr.status < 300 ? res() : rej();
                        xhr.onerror = () => rej();
                    });

                    xhr.send(file);
                    await uploadPromise;

                    // 3. Complete upload
                    const completeRes = await fetch('{{ route('drive.complete') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            storage_path: uploadData.storage_path,
                            display_name: file.name,
                            storage_provider_id: uploadData.storage_provider_id,
                            folder_id: '{{ $currentFolder ? $currentFolder->id : '' }}',
                            mime_type: file.type || 'application/octet-stream',
                            size: file.size,
                            disk: uploadData.disk
                        })
                    });

                    if (!completeRes.ok) {
                        const errorData = await completeRes.json();
                        throw new Error(errorData.error || 'Failed to complete upload metadata');
                    }
                    
                } catch (err) {
                    console.error(err);
                    alert(`Upload failed for ${file.name}`);
                    hasError = true;
                    break;
                }
            }

            this.isUploading = false;
            if (!hasError) window.location.reload();
        }
    }));
});
</script>

<div class="flex flex-col md:flex-row h-full w-full gap-6 max-w-7xl mx-auto" x-data="driveData">
    <!-- Top Action Bar for mobile / Breadcrumbs -->
    <div class="w-full flex-1 flex flex-col min-w-0 relative min-h-screen"
         @click="contextMenuOpen = false"
         @contextmenu.prevent="if($event.target.closest('.group') === null) showContextMenu($event, 'bg')"
         @dragover.prevent="checkDragOver($event)"
         @dragleave.prevent="isDragging = false"
         @drop.prevent="isDragging = false; handleDrop($event)">

         <!-- Drag Overlay -->
         <input type="file" id="hiddenFileInput" class="hidden" multiple @change="if($event.target.files.length) uploadFiles($event.target.files)">
         
         <div x-show="isDragging" class="absolute inset-0 z-50 bg-blue-50/90 border-4 border-dashed border-blue-500 rounded-3xl flex items-center justify-center backdrop-blur-sm pointer-events-none" x-transition x-cloak>
            <div class="text-center">
                <i data-lucide="upload-cloud" class="w-16 h-16 text-blue-600 mx-auto mb-4 pointer-events-none animate-bounce"></i>
                <h2 class="text-2xl font-bold text-blue-700 pointer-events-none">Drop file to upload</h2>
            </div>
         </div>

         <!-- Upload Progress -->
         <div x-show="isUploading" class="absolute inset-0 z-50 bg-white/90 rounded-3xl flex items-center justify-center backdrop-blur-sm" x-cloak>
            <div class="text-center w-64 bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                <i data-lucide="loader" class="w-8 h-8 text-blue-600 mx-auto mb-4 animate-spin"></i>
                <h2 class="text-sm font-bold text-gray-800 mb-3">Uploading... <span x-text="uploadProgress"></span>%</h2>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-300" :style="`width: ${uploadProgress}%`"></div>
                </div>
            </div>
         </div>

        <div class="flex items-center justify-between mb-6 relative z-10">
            <div class="flex items-center text-xl font-bold text-gray-800 tracking-tight gap-2 overflow-x-auto whitespace-nowrap hide-scrollbar pb-1">
                <a href="{{ route('drive.index') }}" class="hover:underline flex items-center px-2 py-1 rounded-lg transition-colors border border-transparent"
                   :class="{ 'bg-blue-50 border-blue-200 text-blue-700': dragHoverFolder === 'root' }"
                   @dragover.prevent="if(draggedType) dragHoverFolder = 'root'"
                   @dragleave.prevent="if(dragHoverFolder === 'root') dragHoverFolder = null"
                   @drop.prevent="handleInternalDrop('', $event)">
                    <i data-lucide="hard-drive" class="w-5 h-5 mr-2 text-blue-600"></i> My Drive
                </a>
                
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $bc)
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400"></i>
                        <a href="{{ route('drive.index', $bc->id) }}" class="hover:underline px-2 py-1 rounded-lg border border-transparent transition-colors {{ $loop->last ? 'text-blue-600' : 'text-gray-600' }}"
                           :class="{ 'bg-blue-50 border-blue-200 text-blue-700': dragHoverFolder === {{ $bc->id }} }"
                           @dragover.prevent="if(draggedType && (draggedType !== 'folder' || draggedId !== {{ $bc->id }})) dragHoverFolder = {{ $bc->id }}"
                           @dragleave.prevent="if(dragHoverFolder === {{ $bc->id }}) dragHoverFolder = null"
                           @drop.prevent="handleInternalDrop({{ $bc->id }}, $event)">
                            {{ $bc->name }}
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 shrink-0">
                <button @click="showNewFolderModal = true; folderToRename = null; folderName = ''" class="flex items-center px-4 py-2 bg-gray-800 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition-all">
                    <i data-lucide="folder-plus" class="w-4 h-4 mr-2"></i> New Folder
                </button>
            </div>
        </div>

        <!-- Folders Section -->
        @if($folders->count() > 0)
        <div class="mb-8">
            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Folders</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                @foreach($folders as $f)
                    <div class="bg-white rounded-xl border border-gray-100 p-3 flex items-center shadow-sm hover:shadow hover:border-blue-200 transition-all group cursor-pointer"
                         :class="{ 'ring-2 ring-blue-500 bg-blue-50': dragHoverFolder === '{{ $f->id }}' }"
                         @click="window.location='{{ route('drive.index', $f->id) }}'"
                         @contextmenu.prevent.stop="showContextMenu($event, 'folder', { id: '{{ $f->id }}', name: '{{ addslashes($f->name) }}' })"
                         draggable="true" 
                         @dragstart="startDrag('folder', '{{ $f->id }}', $event)"
                         @dragover.prevent="if(draggedType && (draggedType !== 'folder' || draggedId !== '{{ $f->id }}')) dragHoverFolder = '{{ $f->id }}'"
                         @dragleave.prevent="dragHoverFolder = null"
                         @drop.prevent="handleInternalDrop('{{ $f->id }}', $event)">
                        
                        <i data-lucide="folder" class="w-6 h-6 text-yellow-400 fill-yellow-400 mr-3 shrink-0"></i>
                        <span class="text-xs font-bold text-gray-700 truncate flex-1" title="{{ $f->name }}">{{ $f->name }}</span>
                        
                        <!-- Removed 3-dot dropdown favoring unified Right Click contextual flows -->
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Files Section -->
        <div>
            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Files</h3>
            
            @if($files->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($files as $file)
                        <!-- Mimic File Grid from traditional index, but slightly modernized for Drive -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group hover:border-blue-200 transition-all flex flex-col cursor-pointer"
                             draggable="true" @dragstart="startDrag('file', '{{ $file->id }}', $event)"
                             @click="openPreviewModal('{{ route('documents.preview', $file->id) }}', '{{ addslashes($file->display_name) }}')"
                             @contextmenu.prevent.stop="showContextMenu($event, 'file', { id: '{{ $file->id }}', name: '{{ addslashes($file->display_name) }}' })">
                            <div class="aspect-[4/3] bg-gray-50 flex items-center justify-center relative group-hover:bg-blue-50 transition-colors">
                                @if($file->thumbnail_path)
                                    <img src="{{ route('documents.thumbnail', $file->id) }}" class="w-full h-full object-cover">
                                @elseif(str_contains($file->mime_type, 'image'))
                                    <img src="{{ route('documents.preview', $file->id) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex flex-col items-center">
                                        @php
                                            $icon = 'file-text';
                                            if (str_contains($file->mime_type, 'pdf')) $icon = 'file-type-2';
                                            if (str_contains($file->mime_type, 'zip') || str_contains($file->mime_type, 'rar')) $icon = 'archive';
                                            if (str_contains($file->mime_type, 'video')) $icon = 'video';
                                            if (str_contains($file->mime_type, 'audio')) $icon = 'music';
                                        @endphp
                                        <i data-lucide="{{ $icon }}" class="w-12 h-12 text-blue-200 group-hover:text-blue-300 transition-colors"></i>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 mt-2">{{ $file->extension }}</span>
                                    </div>
                                @endif
                                
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                    <button @click.stop.prevent="openPreviewModal('{{ route('documents.preview', $file->id) }}', '{{ addslashes($file->display_name) }}')" class="p-2 bg-white rounded-lg hover:text-blue-600 transition-colors" title="View">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <a href="{{ route('documents.download', $file->id) }}" @click.stop class="p-2 bg-white rounded-lg hover:text-green-600 transition-colors" title="Download">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                    <button @click.stop.prevent="openMoveModal('file', '{{ $file->id }}')" class="p-2 bg-white rounded-lg hover:text-purple-600 transition-colors" title="Move">
                                        <i data-lucide="folder-output" class="w-4 h-4"></i>
                                    </button>
                                    <button @click.stop.prevent="openShareModal('{{ $file->id }}', '{{ addslashes($file->display_name) }}')" class="p-2 bg-white rounded-lg hover:text-blue-500 transition-colors" title="Share">
                                        <i data-lucide="share-2" class="w-4 h-4"></i>
                                    </button>
                                    <button @click.stop.prevent="openDeleteModal('file', '{{ addslashes($file->display_name) }}', '{{ route('documents.destroy', $file->id) }}')" class="p-2 bg-white rounded-lg hover:text-red-600 transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-3 border-t border-gray-50 flex items-center">
                                <i data-lucide="file-text" class="w-4 h-4 text-blue-500 mr-2 shrink-0"></i>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $file->display_name }}">{{ $file->display_name }}</h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-16 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-500 tracking-tight">Folder is empty</p>
                    <p class="text-xs text-gray-400 mt-1">Drop files here or click New UI Upload (Coming next!)</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modals (Folder Create/Rename) -->
    <!-- Create Folder -->
    <div x-show="showNewFolderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.away="showNewFolderModal = false">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800">New Folder</h3>
            </div>
            <form action="{{ route('web.folders.store') }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
                <div class="p-5">
                    <input type="text" name="name" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required autofocus placeholder="Folder name">
                </div>
                <div class="px-5 py-3 bg-gray-50 text-right space-x-2">
                    <button type="button" @click="showNewFolderModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rename Folder -->
    <div x-show="folderToRename" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.away="folderToRename = null">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800">Rename Folder</h3>
            </div>
            <form :action="`/folders/${folderToRename}`" method="POST">
                @csrf @method('PUT')
                <div class="p-5">
                    <input type="text" name="name" x-model="folderName" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required autofocus>
                </div>
                <div class="px-5 py-3 bg-gray-50 text-right space-x-2">
                    <button type="button" @click="folderToRename = null" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">Rename</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Modal -->
    <div x-show="showMoveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.away="showMoveModal = false">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800">Move <span x-text="moveType" class="capitalize"></span></h3>
            </div>
            <form :action="moveTargetUrl" method="POST">
                @csrf @method('PUT')
                <div class="p-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Select Destination</label>
                    <select :name="moveType === 'folder' ? 'parent_id' : 'folder_id'" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">My Drive (Root)</option>
                        @foreach($allFolders as $availableFolder)
                            <option value="{{ $availableFolder->id }}">{{ $availableFolder->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="px-5 py-3 bg-gray-50 text-right space-x-2">
                    <button type="button" @click="showMoveModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">Move</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all" @click.away="showDeleteModal = false"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100 shadow-sm">
                    <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Delete <span x-text="deleteType" class="capitalize"></span>?</h3>
                <p class="text-xs text-gray-500 mb-8 leading-relaxed">
                    Are you sure you want to delete <br><strong x-text="deleteTitle" class="text-gray-800"></strong>?<br>
                    <span class="text-red-400 font-medium">This action cannot be undone.</span>
                </p>
                <form :action="deleteFormAction" method="POST">
                    @csrf @method('DELETE')
                    <div class="flex space-x-3 w-full">
                        <button type="button" @click="showDeleteModal = false" class="flex-1 py-3 px-4 text-xs font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-800 rounded-xl transition-all shadow-sm">Cancel</button>
                        <button type="submit" class="flex-1 py-3 px-4 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-all focus:ring-4 focus:ring-red-500/20 shadow-red-600/20">Delete Forever</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div x-show="contextMenuOpen" @click.away="contextMenuOpen = false" x-ref="ctxMenu"
         class="fixed z-[100] w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-1"
         :style="`left: ${contextMenuX}px; top: ${contextMenuY}px;`"
         x-transition.opacity.duration.150ms
         x-cloak>
         
         <!-- Background specific options -->
         <template x-if="contextMenuType === 'bg'">
            <div>
                <button @click="showNewFolderModal = true; folderToRename = null; folderName = ''; contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="folder-plus" class="w-4 h-4 mr-3 text-gray-400"></i> New Folder
                </button>
                <div class="h-px bg-gray-100 my-1 w-full"></div>
                <button @click="contextMenuOpen = false; document.getElementById('hiddenFileInput').click()" class="w-full text-left px-4 py-2.5 text-xs text-blue-700 hover:bg-blue-50 flex items-center font-bold">
                    <i data-lucide="upload-cloud" class="w-4 h-4 mr-3 text-blue-500"></i> Upload File
                </button>
            </div>
         </template>

         <!-- Folder specific options -->
         <template x-if="contextMenuType === 'folder'">
            <div>
                <button @click="openRenameModal(contextMenuFolder.id, contextMenuFolder.name); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="edit-2" class="w-4 h-4 mr-3 text-gray-400"></i> Rename
                </button>
                <button @click="openMoveModal('folder', contextMenuFolder.id); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="folder-output" class="w-4 h-4 mr-3 text-gray-400"></i> Move To
                </button>
                <button @click="openShareModal(contextMenuFolder.id, contextMenuFolder.name); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="share-2" class="w-4 h-4 mr-3 text-indigo-400"></i> Share Folder
                </button>
                <div class="h-px bg-gray-100 my-1 w-full"></div>
                <button @click="openDeleteModal('folder', contextMenuFolder.name, '{{ url('folders') }}/' + contextMenuFolder.id); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 flex items-center font-medium">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-3 text-red-500"></i> Delete Folder
                </button>
            </div>
         </template>

         <!-- File specific options -->
         <template x-if="contextMenuType === 'file'">
            <div>
                <button @click="openPreviewModal(`{{ url('documents') }}/${contextMenuFile.id}/preview`, contextMenuFile.display_name); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-400"></i> Preview
                </button>
                <a :href="`{{ url('documents') }}/${contextMenuFile.id}/download`" @click="contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="download-cloud" class="w-4 h-4 mr-3 text-green-400"></i> Download
                </a>
                <div class="h-px bg-gray-100 my-1 w-full"></div>
                <button @click="openShareModal(contextMenuFile.id, contextMenuFile.display_name); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="share-2" class="w-4 h-4 mr-3 text-indigo-400"></i> Share
                </button>
                <button @click="openMoveModal('file', contextMenuFile.id); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 flex items-center">
                    <i data-lucide="folder-output" class="w-4 h-4 mr-3 text-gray-400"></i> Move To
                </button>
                <div class="h-px bg-gray-100 my-1 w-full"></div>
                <button @click="openDeleteModal('file', contextMenuFile.display_name, '{{ url('documents') }}/' + contextMenuFile.id); contextMenuOpen = false" class="w-full text-left px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 flex items-center font-medium">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-3 text-red-500"></i> Delete File
                </button>
            </div>
         </template>
    </div>

    @include('share.modal')

    <!-- Preview Modal -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col overflow-hidden" @click.away="showPreviewModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-50 rounded-xl">
                        <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800" x-text="previewName"></h3>
                        <p class="text-[10px] text-gray-400 font-medium">File Preview</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" download class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Download">
                        <i data-lucide="download" class="w-5 h-5"></i>
                    </a>
                    <button @click="showPreviewModal = false" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-gray-100 relative">
                <template x-if="showPreviewModal">
                    <iframe :src="previewUrl" class="w-full h-full border-none" @load="$el.classList.remove('opacity-0')" class="transition-opacity duration-300"></iframe>
                </template>
                <div class="absolute inset-0 flex items-center justify-center -z-10">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>
@endsection
