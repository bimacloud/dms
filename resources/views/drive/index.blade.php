@extends('layouts.app')

@section('header', 'Drive Saya')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('driveData', () => ({
        showNewFolderModal: false,
        folderToRename: null,
        folderName: '',
        showMoveModal: false,
        moveType: 'dokumen',
        moveId: null,
        moveTargetUrl: '',
        showDeleteModal: false,
        deleteFormAction: '',
        deleteTitle: '',
        deleteType: '',
        showShareModal: {{ (session('success') && session('share_link')) ? 'true' : 'false' }},
        shareModalDocId: '',
        shareModalDocTitle: '',
        shareModalDocType: 'file',
        fileToRename: null,
        allFolders: {{ Js::from($allFolders) }},
        selectedFolderId: '',
        
        // Preview State
        showPreviewModal: false,
        previewUrl: '',
        previewName: '',
        previewMimeType: '',
        zoomLevel: 100,
        isMaximized: false,

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

        zoomIn() { if (this.zoomLevel < 300) this.zoomLevel += 25 },
        zoomOut() { if (this.zoomLevel > 25) this.zoomLevel -= 25 },
        toggleMaximize() { 
            this.isMaximized = !this.isMaximized; 
            this.zoomLevel = 100;
            setTimeout(() => lucide.createIcons(), 10);
        },

        openPreviewModal(url, name, mimeType = '') {
            this.previewUrl = url;
            this.previewName = name;
            this.previewMimeType = mimeType || '';
            this.zoomLevel = 100;
            this.isMaximized = false;
            this.showPreviewModal = true;
            setTimeout(() => lucide.createIcons(), 50);
        },

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
                    let x = e.clientX;
                    let y = e.clientY;
                    
                    if (x + rect.width > window.innerWidth) {
                        x -= rect.width;
                    }
                    if (y + rect.height > window.innerHeight) {
                        y -= rect.height;
                    }
                    
                    if (x < 8) x = 8;
                    if (y < 8) y = 8;
                    
                    if (x + rect.width > window.innerWidth - 8) {
                        x = window.innerWidth - rect.width - 8;
                    }
                    if (y + rect.height > window.innerHeight - 8) {
                        y = window.innerHeight - rect.height - 8;
                    }
                    
                    this.contextMenuX = x;
                    this.contextMenuY = y;
                }
            });
            
            this.contextMenuOpen = true;
        },
        openShareModal(id, title, type = 'file') {
            this.shareModalDocId = id;
            this.shareModalDocTitle = title;
            this.shareModalDocType = type;
            this.showShareModal = true;
        },
        openDeleteModal(type, title, actionTarget) {
            this.deleteType = type === 'folder' ? 'Folder' : 'File';
            this.deleteTitle = title;
            this.deleteFormAction = actionTarget;
            this.showDeleteModal = true;
        },
        openRenameModal(folderId, name) {
            this.folderToRename = folderId;
            this.folderName = name;
            this.showNewFolderModal = false;
        },
        openFileRenameModal(fileId, name) {
            this.fileToRename = fileId;
            this.fileName = name;
        },
        openMoveModal(type, id) {
            this.moveType = type === 'folder' ? 'Folder' : 'File';
            this.moveId = id;
            this.selectedFolderId = '';
            this.moveTargetUrl = type === 'folder' 
                ? '{{ url('folders') }}/' + id
                : '{{ url('documents') }}/' + id;
            this.showMoveModal = true;
        },
        getSelectedFolderName() {
            if (this.selectedFolderId === '') return 'Drive Utama (Root)';
            const folder = this.allFolders.find(f => f.id === this.selectedFolderId);
            return folder ? folder.name : 'Drive Utama (Root)';
        },
        isFolderDisabled(id, pathString) {
            if (this.moveType !== 'Folder') return false;
            if (this.moveId === id) return true;
            const parents = pathString ? pathString.split(',') : [];
            return parents.includes(this.moveId);
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
                    
                    if (!startRes.ok) throw new Error('Gagal');
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
                        throw new Error(errorData.error || 'Gagal melengkapi metadata');
                    }
                    
                } catch (err) {
                    console.error(err);
                    alert(`Gagal mengunggah ${file.name}`);
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

<div class="flex flex-col h-full w-full gap-6 max-w-7xl mx-auto" x-data="driveData">
    <!-- Top Action Bar -->
    <div class="w-full flex-1 flex flex-col min-w-0 relative min-h-screen"
         @click="contextMenuOpen = false"
         @contextmenu.prevent="if($event.target.closest('.group') === null) showContextMenu($event, 'bg')"
         @dragover.prevent="checkDragOver($event)"
         @dragleave.prevent="isDragging = false"
         @drop.prevent="isDragging = false; handleDrop($event)">

         <!-- Drag Overlay -->
         <input type="file" id="hiddenFileInput" class="hidden" multiple @change="if($event.target.files.length) uploadFiles($event.target.files)">
         
         <div x-show="isDragging" class="absolute inset-0 z-50 bg-blue-50/90 border-4 border-dashed border-blue-500 rounded-[3rem] flex items-center justify-center backdrop-blur-sm pointer-events-none" x-transition x-cloak>
            <div class="text-center">
                <i data-lucide="upload-cloud" class="w-16 h-16 text-blue-600 mx-auto mb-4 pointer-events-none animate-bounce"></i>
                <h2 class="text-2xl font-bold text-blue-700 pointer-events-none">Lepaskan untuk mengunggah</h2>
            </div>
         </div>

         <!-- Upload Progress -->
         <div x-show="isUploading" class="absolute inset-0 z-[150] bg-white/90 rounded-[3rem] flex items-center justify-center backdrop-blur-sm" x-cloak>
            <div class="text-center w-64 bg-white p-8 rounded-3xl shadow-2xl border border-gray-100">
                <i data-lucide="loader" class="w-10 h-10 text-blue-600 mx-auto mb-4 animate-spin"></i>
                <h2 class="text-sm font-bold text-gray-800 mb-4">Mengunggah... <span x-text="uploadProgress"></span>%</h2>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-300" :style="`width: ${uploadProgress}%`"></div>
                </div>
            </div>
         </div>

        <div class="flex items-center justify-between mb-8 relative z-10 bg-white/50 backdrop-blur-md p-4 rounded-3xl border border-gray-100">
            <div class="flex items-center text-lg font-bold text-gray-800 tracking-tight gap-2 overflow-x-auto whitespace-nowrap hide-scrollbar">
                <a href="{{ route('drive.index') }}" class="hover:bg-blue-50 hover:text-blue-600 flex items-center px-4 py-2 rounded-2xl transition-all border border-transparent"
                   :class="{ 'bg-blue-50 border-blue-200 text-blue-700': dragHoverFolder === 'root' }"
                   @dragover.prevent="if(draggedType) dragHoverFolder = 'root'"
                   @dragleave.prevent="if(dragHoverFolder === 'root') dragHoverFolder = null"
                   @drop.prevent="handleInternalDrop('', $event)">
                    <i data-lucide="hard-drive" class="w-5 h-5 mr-2 text-blue-600"></i> Drive Saya
                </a>
                
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $bc)
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
                        <a href="{{ route('drive.index', $bc->id) }}" class="hover:bg-blue-50 px-4 py-2 rounded-2xl border border-transparent transition-all {{ $loop->last ? 'text-blue-600 bg-blue-50/50' : 'text-gray-500' }}"
                           :class="{ 'bg-blue-50 border-blue-200 text-blue-700': dragHoverFolder === {{ $bc->id }} }"
                           @dragover.prevent="if(draggedType && (draggedType !== 'folder' || draggedId !== {{ $bc->id }})) dragHoverFolder = {{ $bc->id }}"
                           @dragleave.prevent="if(dragHoverFolder === {{ $bc->id }}) dragHoverFolder = null"
                           @drop.prevent="handleInternalDrop({{ $bc->id }}, $event)">
                            {{ $bc->name }}
                        </a>
                    @endforeach
                @endif
            </div>

            <div class="hidden md:flex gap-3 shrink-0">
                <button @click="document.getElementById('hiddenFileInput').click()" class="flex items-center px-6 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95">
                    <i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> Unggah
                </button>
                <button @click="showNewFolderModal = true; folderToRename = null; folderName = ''" class="flex items-center px-6 py-2.5 bg-gray-800 text-white text-xs font-bold rounded-2xl shadow-xl shadow-gray-800/20 hover:bg-gray-900 transition-all active:scale-95">
                    <i data-lucide="folder-plus" class="w-4 h-4 mr-2"></i> Folder Baru
                </button>
            </div>
        </div>

        <!-- Folders Section -->
        @if($folders->count() > 0)
        <div class="mb-10">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-2">Folder</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($folders as $f)
                    <div class="bg-white rounded-3xl border border-gray-100 p-4 flex items-center shadow-sm hover:shadow-xl hover:border-blue-200 transition-all group cursor-pointer"
                         :class="{ 'ring-2 ring-blue-500 bg-blue-50': dragHoverFolder === '{{ $f->id }}' }"
                         @click="window.location='{{ route('drive.index', $f->id) }}'"
                         @contextmenu.prevent.stop="showContextMenu($event, 'folder', { id: '{{ $f->id }}', name: '{{ addslashes($f->name) }}' })"
                         draggable="true" 
                         @dragstart="startDrag('folder', '{{ $f->id }}', $event)"
                         @dragover.prevent="if(draggedType && (draggedType !== 'folder' || draggedId !== '{{ $f->id }}')) dragHoverFolder = '{{ $f->id }}'"
                         @dragleave.prevent="dragHoverFolder = null"
                         @drop.prevent="handleInternalDrop('{{ $f->id }}', $event)">
                        
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center mr-4 group-hover:bg-amber-100 transition-all">
                            <i data-lucide="folder" class="w-6 h-6 text-amber-500 fill-amber-500"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-700 truncate flex-1" title="{{ $f->name }}">{{ $f->name }}</span>
                        <button @click.stop.prevent="showContextMenu($event, 'folder', { id: '{{ $f->id }}', name: '{{ addslashes($f->name) }}' })"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all shrink-0 ml-1">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Files Section -->
        <div class="flex-1">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-2">File</h3>
            
            @if($files->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    @foreach($files as $file)
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden group hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-500/10 transition-all flex flex-col cursor-pointer relative"
                             draggable="true" @dragstart="startDrag('file', '{{ $file->id }}', $event)"
                             @click="openPreviewModal('{{ route('documents.preview', $file->id) }}', '{{ addslashes($file->display_name) }}', '{{ $file->mime_type }}')"
                             @contextmenu.prevent.stop="showContextMenu($event, 'file', { id: '{{ $file->id }}', name: '{{ addslashes($file->display_name) }}', mime_type: '{{ $file->mime_type }}' })">
                            
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
                                        <div class="w-16 h-16 bg-blue-100/50 rounded-2xl flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
                                            <i data-lucide="{{ $icon }}" class="w-8 h-8 text-blue-500"></i>
                                        </div>
                                        <span class="text-[9px] uppercase font-black text-gray-400 tracking-widest">{{ $file->extension ?: 'FILE' }}</span>
                                    </div>
                                @endif
                                
                                <div class="absolute inset-0 bg-gray-900/40 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-2 backdrop-blur-[2px]">
                                    <button @click.stop.prevent="openPreviewModal('{{ route('documents.preview', $file->id) }}', '{{ addslashes($file->display_name) }}', '{{ $file->mime_type }}')" class="w-10 h-10 bg-white text-gray-700 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-lg flex items-center justify-center" title="Pratinjau">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                    <a href="{{ route('documents.download', $file->id) }}" @click.stop class="w-10 h-10 bg-white text-gray-700 rounded-xl hover:bg-green-600 hover:text-white transition-all shadow-lg flex items-center justify-center" title="Unduh">
                                        <i data-lucide="download" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <h4 class="text-xs font-bold text-gray-800 truncate flex-1" title="{{ $file->display_name }}">{{ $file->display_name }}</h4>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $file->size_formatted ?? ($file->size . ' B') }}</span>
                                    <button @click.stop.prevent="showContextMenu($event, 'file', { id: '{{ $file->id }}', name: '{{ addslashes($file->display_name) }}', mime_type: '{{ $file->mime_type }}' })"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all">
                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-24 text-center bg-white rounded-[3rem] border border-dashed border-gray-200 shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Folder Kosong</h3>
                    <p class="text-sm text-gray-400 mt-2">Tarik file ke sini atau klik tombol Unggah untuk memulai.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <div x-show="showNewFolderModal || folderToRename" class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md" x-cloak>
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden" @click.away="showNewFolderModal = false; folderToRename = null">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-900" x-text="folderToRename ? 'Ubah Nama Folder' : 'Folder Baru'"></h3>
                <i data-lucide="folder-plus" class="w-6 h-6 text-blue-500"></i>
            </div>
            <form :action="folderToRename ? `/folders/${folderToRename}` : '{{ route('web.folders.store') }}'" method="POST">
                @csrf 
                <template x-if="folderToRename"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
                <div class="p-8">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Folder</label>
                    <input type="text" name="name" x-model="folderName" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required autofocus placeholder="Masukkan nama folder...">
                </div>
                <div class="px-8 py-6 bg-gray-50/50 flex gap-3">
                    <button type="button" @click="showNewFolderModal = false; folderToRename = null" class="flex-1 py-3 text-xs font-bold text-gray-500 hover:text-gray-700 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95" x-text="folderToRename ? 'Simpan' : 'Buat Folder'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-xl" x-cloak>
        <div class="bg-white rounded-[3rem] shadow-2xl w-full max-w-sm overflow-hidden transform transition-all" @click.away="showDeleteModal = false">
            <div class="p-10 text-center">
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-100 shadow-inner">
                    <i data-lucide="trash-2" class="w-10 h-10 text-red-500"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Hapus <span x-text="deleteType"></span>?</h3>
                <p class="text-sm text-gray-400 mb-10 leading-relaxed px-4">
                    Apakah Anda yakin ingin menghapus <br><strong x-text="deleteTitle" class="text-gray-900"></strong>?<br>
                    <span class="text-red-500 font-bold mt-2 block">Tindakan ini tidak dapat dibatalkan.</span>
                </p>
                <form :action="deleteFormAction" method="POST">
                    @csrf @method('DELETE')
                    <div class="flex gap-4">
                        <button type="button" @click="showDeleteModal = false" class="flex-1 py-4 text-xs font-bold text-gray-500 hover:text-gray-800 transition-all">Batal</button>
                        <button type="submit" class="flex-1 py-4 bg-red-600 text-white text-xs font-bold rounded-2xl shadow-xl shadow-red-500/20 hover:bg-red-700 transition-all active:scale-95">Hapus Selamanya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rename File Modal -->
    <div x-show="fileToRename" class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md" x-cloak>
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden" @click.away="fileToRename = null">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-900">Ubah Nama File</h3>
                <i data-lucide="edit-3" class="w-6 h-6 text-blue-500"></i>
            </div>
            <form :action="`{{ url('documents') }}/${fileToRename}`" method="POST">
                @csrf 
                <input type="hidden" name="_method" value="PUT">
                <div class="p-8">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama File</label>
                    <input type="text" name="display_name" x-model="fileName" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" required autofocus placeholder="Masukkan nama file...">
                </div>
                <div class="px-8 py-6 bg-gray-50/50 flex gap-3">
                    <button type="button" @click="fileToRename = null" class="flex-1 py-3 text-xs font-bold text-gray-500 hover:text-gray-700 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Modal -->
    <div x-show="showMoveModal" class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md" x-cloak>
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md" @click.away="showMoveModal = false">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-900">Pindahkan <span x-text="moveType"></span></h3>
                <i data-lucide="folder-input" class="w-6 h-6 text-blue-500"></i>
            </div>
            <form :action="moveTargetUrl" method="POST">
                @csrf 
                <input type="hidden" name="_method" value="PUT">
                <div class="p-8 space-y-4">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Folder Tujuan</label>
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <!-- Trigger Button -->
                        <button type="button" @click="dropdownOpen = !dropdownOpen" 
                            class="w-full px-6 py-4 pr-12 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold text-left outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer flex items-center justify-between">
                            <span x-text="getSelectedFolderName()" class="truncate text-gray-700"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        
                        <!-- Hidden real select to submit with form -->
                        <select :name="moveType === 'Folder' ? 'parent_id' : 'folder_id'" x-model="selectedFolderId" class="hidden">
                            <option value="">Drive Utama (Root)</option>
                            @foreach($allFolders as $folderOption)
                                <option value="{{ $folderOption->id }}">{{ $folderOption->name }}</option>
                            @endforeach
                        </select>
                        
                        <!-- Custom Dropdown List -->
                        <div x-show="dropdownOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-[260] w-full mt-2 bg-white rounded-3xl shadow-2xl border border-gray-100 max-h-60 overflow-y-auto p-2 space-y-1" x-cloak>
                            
                            <!-- Root Option -->
                            <button type="button" @click="selectedFolderId = ''; dropdownOpen = false" 
                                class="w-full text-left px-4 py-3 text-xs font-bold rounded-2xl transition-all flex items-center hover:bg-blue-50"
                                :class="selectedFolderId === '' ? 'bg-blue-50 text-blue-600' : 'text-gray-700'">
                                <i data-lucide="hard-drive" class="w-4 h-4 mr-3" :class="selectedFolderId === '' ? 'text-blue-500' : 'text-gray-400'"></i>
                                Drive Utama (Root)
                            </button>
                            
                            <!-- Folder Tree List -->
                            @php
                                $renderTreeList = function($folders, $parentId = null, $depth = 0, $parentPath = []) use (&$renderTreeList) {
                                    foreach ($folders as $folder) {
                                        if ($folder->parent_id === $parentId) {
                                            $currentPath = array_merge($parentPath, [$folder->id]);
                                            $pathString = implode(',', $parentPath);
                                            $indentStyle = 'style="padding-left: ' . (($depth * 16) + 16) . 'px;"';
                                            
                                            echo '
                                            <button type="button" 
                                                @click="if(!isFolderDisabled(\'' . $folder->id . '\', \'' . $pathString . '\')) { selectedFolderId = \'' . $folder->id . '\'; dropdownOpen = false; }"
                                                ' . $indentStyle . '
                                                class="w-full text-left py-3 pr-4 text-xs font-bold rounded-2xl transition-all flex items-center group/opt"
                                                :class="{
                                                    \'bg-blue-50 text-blue-600\': selectedFolderId === \'' . $folder->id . '\',
                                                    \'text-gray-700 hover:bg-gray-50\': selectedFolderId !== \'' . $folder->id . '\' && !isFolderDisabled(\'' . $folder->id . '\', \'' . $pathString . '\'),
                                                    \'opacity-40 cursor-not-allowed text-gray-400\': isFolderDisabled(\'' . $folder->id . '\', \'' . $pathString . '\')
                                                }"
                                                :disabled="isFolderDisabled(\'' . $folder->id . '\', \'' . $pathString . '\')">
                                                <i data-lucide="folder" class="w-4 h-4 mr-2 shrink-0" :class="selectedFolderId === \'' . $folder->id . '\' ? \'text-blue-500 fill-blue-500/20\' : \'text-amber-500 fill-amber-500/20\'"></i>
                                                <span class="truncate">' . e($folder->name) . '</span>
                                            </button>
                                            ';
                                            $renderTreeList($folders, $folder->id, $depth + 1, $currentPath);
                                        }
                                    }
                                };
                                $renderTreeList($allFolders, null, 0);
                            @endphp
                        </div>
                    </div>
                </div>
                <div class="px-8 py-6 bg-gray-50/50 flex gap-3 rounded-b-[2.5rem]">
                    <button type="button" @click="showMoveModal = false" class="flex-1 py-3 text-xs font-bold text-gray-500 hover:text-gray-700 transition-all">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95">Pindahkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Premium Preview Modal -->
    <div x-show="showPreviewModal" 
         class="fixed inset-0 z-[300] flex items-center justify-center bg-gray-900/95 backdrop-blur-3xl transition-all duration-500"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-105" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-105"
         x-cloak>
        
        <!-- Header Toolbar -->
        <div class="absolute top-0 left-0 right-0 p-6 flex items-center justify-between z-[310] bg-gradient-to-b from-black/50 to-transparent">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20">
                    <i data-lucide="file-text" class="w-6 h-6 text-white" x-show="!previewMimeType.startsWith('image/')"></i>
                    <i data-lucide="image" class="w-6 h-6 text-white" x-show="previewMimeType.startsWith('image/')"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white" x-text="previewName"></h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest" x-text="previewMimeType"></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <template x-if="previewMimeType.startsWith('image/')">
                    <div class="flex items-center bg-white/10 backdrop-blur-md rounded-2xl p-1 border border-white/20 mr-4">
                        <button @click="zoomOut()" class="p-2 text-white hover:bg-white/10 rounded-xl transition-all"><i data-lucide="minus" class="w-4 h-4"></i></button>
                        <span class="px-4 text-xs font-black text-white w-16 text-center" x-text="zoomLevel + '%'"></span>
                        <button @click="zoomIn()" class="p-2 text-white hover:bg-white/10 rounded-xl transition-all"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    </div>
                </template>
                
                <button @click="showPreviewModal = false" class="w-12 h-12 bg-white/10 hover:bg-red-500 text-white rounded-2xl backdrop-blur-md border border-white/20 flex items-center justify-center transition-all active:scale-90">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Main Preview Area -->
        <div class="w-full h-full flex items-center justify-center p-12 lg:p-24 overflow-hidden" @click.self="showPreviewModal = false">
            <template x-if="showPreviewModal">
                <div class="w-full h-full flex items-center justify-center">
                    <template x-if="previewMimeType.startsWith('image/')">
                        <div class="relative group cursor-zoom-out" @click="showPreviewModal = false">
                            <img :src="previewUrl" 
                                 :style="`transform: scale(${zoomLevel/100});`"
                                 class="max-w-full max-h-[75vh] rounded-2xl shadow-[0_0_100px_rgba(0,0,0,0.5)] border border-white/10 transition-transform duration-300">
                        </div>
                    </template>

                    <template x-if="previewMimeType === 'application/pdf'">
                        <div class="w-full h-full flex items-center justify-center">
                            <!-- Desktop PDF Preview -->
                            <div class="hidden md:block w-full max-w-5xl h-full bg-white rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/20">
                                <iframe :src="previewUrl" class="w-full h-full border-none"></iframe>
                            </div>
                            
                            <!-- Mobile PDF Preview Fallback -->
                            <div class="md:hidden w-full max-w-sm bg-[#0f172a]/80 backdrop-blur-2xl p-8 rounded-[2.5rem] border border-white/10 text-center shadow-2xl flex flex-col justify-center items-center">
                                <div class="w-16 h-16 bg-red-500/20 rounded-2xl flex items-center justify-center mb-6">
                                    <i data-lucide="file-text" class="w-8 h-8 text-red-500"></i>
                                </div>
                                <h3 class="text-base font-black text-white mb-2 max-w-xs truncate" x-text="previewName"></h3>
                                <p class="text-xs text-slate-400 mb-8 max-w-xs leading-relaxed">PDF Reader browser tidak mendukung pratinjau langsung di dalam aplikasi. Silakan buka dokumen di tab baru.</p>
                                
                                <div class="flex flex-col gap-3 w-full">
                                    <a :href="previewUrl" target="_blank" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-2xl shadow-xl shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                        Buka PDF
                                    </a>
                                    <a :href="previewUrl.replace('/preview', '/download')" class="w-full py-3.5 bg-white/5 hover:bg-white/10 text-white text-xs font-bold rounded-2xl border border-white/10 transition-all flex items-center justify-center gap-2">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                        Unduh
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="previewMimeType.startsWith('video/')">
                        <video controls class="max-w-full max-h-[80vh] rounded-[2.5rem] shadow-2xl border border-white/20" :src="previewUrl" autoplay></video>
                    </template>

                    <template x-if="previewMimeType.startsWith('audio/')">
                        <div class="bg-white/10 backdrop-blur-2xl p-12 rounded-[3rem] border border-white/20 text-center">
                            <div class="w-24 h-24 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-8">
                                <i data-lucide="music" class="w-10 h-10 text-blue-400"></i>
                            </div>
                            <audio controls class="w-80" :src="previewUrl"></audio>
                        </div>
                    </template>

                    <template x-if="!previewMimeType.startsWith('image/') && previewMimeType !== 'application/pdf' && !previewMimeType.startsWith('video/') && !previewMimeType.startsWith('audio/')">
                        <div class="bg-white/10 backdrop-blur-2xl p-16 rounded-[3rem] border border-white/20 text-center">
                            <div class="w-20 h-20 bg-gray-500/20 rounded-3xl flex items-center justify-center mx-auto mb-8">
                                <i data-lucide="file-warning" class="w-10 h-10 text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-black text-white mb-4">Pratinjau Tidak Tersedia</h3>
                            <a :href="previewUrl" download class="px-8 py-4 bg-blue-600 text-white font-bold rounded-2xl shadow-xl hover:bg-blue-700 transition-all block">Unduh Sekarang</a>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Context Menu -->
    <div x-show="contextMenuOpen" @click.away="contextMenuOpen = false" x-ref="ctxMenu"
         class="fixed z-[200] w-64 bg-white rounded-3xl shadow-2xl border border-gray-100 p-2"
         :style="`left: ${contextMenuX}px; top: ${contextMenuY}px;`"
         x-transition.opacity.duration.150ms
         x-cloak>
         
         <template x-if="contextMenuType === 'bg'">
            <button @click="showNewFolderModal = true; contextMenuOpen = false" class="w-full text-left px-5 py-4 text-xs font-black text-gray-700 hover:bg-blue-600 hover:text-white rounded-2xl flex items-center transition-all">
                <i data-lucide="folder-plus" class="w-4 h-4 mr-3"></i> Folder Baru
            </button>
         </template>

         <template x-if="contextMenuType === 'folder'">
            <div class="space-y-1">
                <button @click="openRenameModal(contextMenuFolder.id, contextMenuFolder.name); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="edit-3" class="w-4 h-4 mr-3 text-amber-500"></i> Ubah Nama
                </button>
                <button @click="openMoveModal('folder', contextMenuFolder.id); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="folder-input" class="w-4 h-4 mr-3 text-indigo-500"></i> Pindahkan
                </button>
                <div class="h-px bg-gray-100 my-1 mx-2"></div>
                <button @click="openDeleteModal('folder', contextMenuFolder.name, '{{ url('folders') }}/' + contextMenuFolder.id); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Hapus Folder
                </button>
            </div>
         </template>

         <template x-if="contextMenuType === 'file'">
            <div class="space-y-1">
                <button @click="openPreviewModal(`{{ url('documents') }}/${contextMenuFile.id}/preview`, contextMenuFile.name, contextMenuFile.mime_type); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> Pratinjau
                </button>
                <a :href="`{{ url('documents') }}/${contextMenuFile.id}/download`" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="download-cloud" class="w-4 h-4 mr-3 text-green-500"></i> Unduh
                </a>
                <button @click="openFileRenameModal(contextMenuFile.id, contextMenuFile.name); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="edit-3" class="w-4 h-4 mr-3 text-amber-500"></i> Ubah Nama
                </button>
                <button @click="openMoveModal('file', contextMenuFile.id); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="folder-input" class="w-4 h-4 mr-3 text-indigo-500"></i> Pindahkan
                </button>
                <div class="h-px bg-gray-100 my-1 mx-2"></div>
                <button @click="openDeleteModal('file', contextMenuFile.name, '{{ url('documents') }}/' + contextMenuFile.id); contextMenuOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 rounded-2xl flex items-center transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Hapus File
                </button>
            </div>
         </template>
    </div>

     <!-- Mobile Floating Action Button (FAB) -->
     <div class="md:hidden fixed bottom-6 right-6 z-40" x-data="{ fabOpen: false }" @click.away="fabOpen = false">
         <!-- FAB Options Menu -->
         <div x-show="fabOpen" 
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 translate-y-4 scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 translate-y-0 scale-100"
              x-transition:leave-end="opacity-0 translate-y-4 scale-95"
              class="absolute bottom-16 right-0 mb-2 w-48 bg-white rounded-3xl shadow-2xl border border-gray-100 p-2 space-y-1" x-cloak>
             
             <button @click="document.getElementById('hiddenFileInput').click(); fabOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                 <i data-lucide="upload-cloud" class="w-4 h-4 mr-3 text-blue-600"></i> Unggah File
             </button>
             <button @click="showNewFolderModal = true; folderToRename = null; folderName = ''; fabOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 rounded-2xl flex items-center transition-all">
                 <i data-lucide="folder-plus" class="w-4 h-4 mr-3 text-amber-500"></i> Folder Baru
             </button>
         </div>

         <!-- Main FAB Button -->
         <button @click="fabOpen = !fabOpen" 
             class="w-14 h-14 bg-blue-600 text-white rounded-full flex items-center justify-center shadow-2xl shadow-blue-500/40 hover:bg-blue-700 transition-all active:scale-90 transform focus:outline-none">
             <!-- We use raw svg or style for plus since Lucide icons are parsed once. Alternatively, we can let Lucide update it or use standard inline icon -->
             <i data-lucide="plus" class="w-6 h-6 transition-transform duration-200" :class="{ 'rotate-45': fabOpen }"></i>
         </button>
     </div>

    @include('share.modal')
</div>

<!-- Mobile Drag and Drop Polyfill for Android/Touch Devices -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mobile-drag-drop@3.0.0-rc.1/default.css">
<script src="https://cdn.jsdelivr.net/npm/mobile-drag-drop@3.0.0-rc.1/index.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mobile-drag-drop@3.0.0-rc.1/scroll-behaviour.min.js"></script>
<script>
    // Initialize touch-drag polyfill. To scroll easily, drag is triggered after holding the element for 400ms.
    window.addEventListener('touchmove', function() {}, {passive: false});
    MobileDragDrop.polyfill({
        dragImageTranslateOverride: MobileDragDrop.scrollBehaviourDragImageTranslateOverride,
        holdToDrag: 400
    });
</script>
@endsection
