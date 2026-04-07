@extends('layouts.app')

@section('header', 'Documents')

@section('content')
<div x-data="{ 
    showUpload: false, 
    previewUrl: null, 
    previewType: null,
    shareModalDocId: null,
    shareModalDocTitle: null,
    shareModalDocType: 'file',
    showShareModal: {{ (session('success') && session('share_link')) ? 'true' : 'false' }},
    filePreview: null,
    fileName: '',
    isUploading: false,
    isDragging: false,
    contextMenuOpen: false,
    contextMenuX: 0,
    contextMenuY: 0,
    showContextMenu(e) {
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
    processFile(files) {
        if (!files || files.length === 0) return;
        
        const dataTransfer = new DataTransfer();
        Array.from(files).forEach(f => dataTransfer.items.add(f));
        document.getElementById('globalFileInput').files = dataTransfer.files;

        if (files.length > 1) {
            this.fileName = files.length + ' Files Selected';
            this.filePreview = 'multiple';
        } else {
            const file = files[0];
            this.fileName = file.name;
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => { this.filePreview = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.filePreview = 'pdf';
            }
        }
        
        this.showUpload = true;
        lucide.createIcons();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    handleGlobalDrop(e) {
        this.isDragging = false;
        if(e.dataTransfer.files.length) this.processFile(e.dataTransfer.files);
    },
    handleFile(e) {
        if(e.target.files.length) this.processFile(e.target.files);
    },
    triggerUploadDialog() {
        this.contextMenuOpen = false;
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.onchange = (e) => {
            if(e.target.files.length) this.processFile(e.target.files);
        };
        input.click();
    },
    openShareModal(id, title, type = 'file') {
        this.shareModalDocId = id;
        this.shareModalDocTitle = title;
        this.shareModalDocType = type;
        this.showShareModal = true;
    },
    closePreview() { this.previewUrl = null; this.previewType = null; }
}"
@click="contextMenuOpen = false"
@contextmenu.prevent="if($event.target.closest('.group') === null && $event.target.closest('.bg-white') === null) showContextMenu($event)"
@dragover.prevent="isDragging = true"
@dragleave.prevent="isDragging = false"
@drop.prevent="handleGlobalDrop($event)"
class="relative min-h-screen">
    <!-- Compact Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Document Gallery</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage and organize your digital files.</p>
        </div>
        <button @click="showUpload = !showUpload" 
            class="flex items-center px-4 py-2 text-xs font-bold text-white rounded-xl transition-all shadow-sm"
            :class="showUpload ? 'bg-gray-800 hover:bg-gray-700' : 'bg-blue-600 hover:bg-blue-500'">
            <i :data-lucide="showUpload ? 'chevron-up' : 'plus'" class="w-4 h-4 mr-2"></i>
            <span x-text="showUpload ? 'Close' : 'Add Document'"></span>
        </button>
    </div>

    <!-- Simple Inline Uploader -->
    <div x-show="showUpload" x-transition class="mb-8" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" @submit="isUploading = true" class="p-5">
                @csrf
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Drop Zone -->
                    <div class="lg:w-1/3 relative group min-h-[140px] rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 hover:bg-blue-50 transition-colors flex items-center justify-center cursor-pointer">
                        <template x-if="!filePreview">
                            <div class="text-center p-4">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-blue-500 mx-auto mb-1"></i>
                                <p class="text-[10px] font-bold text-gray-600">Drag file or click</p>
                            </div>
                        </template>
                        <template x-if="filePreview">
                            <div class="text-center p-4 relative z-10">
                                <template x-if="filePreview === 'pdf'">
                                    <i data-lucide="file-text" class="w-10 h-10 text-red-500 mx-auto"></i>
                                </template>
                                <template x-if="filePreview === 'multiple'">
                                    <i data-lucide="files" class="w-10 h-10 text-indigo-500 mx-auto"></i>
                                </template>
                                <template x-if="filePreview !== 'pdf' && filePreview !== 'multiple'">
                                    <img :src="filePreview" class="w-20 h-16 object-cover rounded shadow-sm mx-auto">
                                </template>
                                <p class="text-[9px] font-bold text-gray-500 mt-2 truncate max-w-[120px]" x-text="fileName"></p>
                                <button type="button" @click="filePreview = null; fileName = ''; document.getElementById('globalFileInput').value = ''" class="mt-1 text-[9px] font-bold text-red-500 hover:underline">Change</button>
                            </div>
                        </template>
                        <input type="file" name="files[]" id="globalFileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required multiple @change="handleFile">
                    </div>

                    <!-- Inputs & Action -->
                    <div class="lg:flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1" x-show="filePreview !== 'multiple'">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">File Name</label>
                            <input type="text" name="display_name" class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all" :required="filePreview !== 'multiple'" placeholder="e.g. Invoice #123">
                        </div>
                        <div class="space-y-1 text-right" :class="{'md:col-span-2 text-left': filePreview === 'multiple'}">
                            <label class="text-[10px] font-bold text-gray-400 uppercase text-left block">Category</label>
                            <select name="category_id" class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all cursor-pointer" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 pt-2 flex items-center justify-between border-t border-gray-50">
                            <div x-show="isUploading" class="flex-1 mr-4 bg-gray-100 rounded-full h-1 overflow-hidden" x-cloak>
                                <div class="bg-blue-600 h-full animate-pulse w-full"></div>
                            </div>
                            <button type="submit" 
                                class="ml-auto flex items-center bg-blue-600 text-white px-6 py-2.5 rounded-xl text-xs font-bold hover:bg-blue-700 transition-all disabled:opacity-50"
                                :disabled="isUploading">
                                <i data-lucide="check" class="w-4 h-4 mr-2" x-show="!isUploading"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="isUploading" x-cloak></i>
                                <span x-text="isUploading ? 'Uploading...' : 'Save Document'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-6">
        <form action="{{ route('documents.index') }}" method="GET" class="flex flex-1 gap-2 max-w-lg">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-100 bg-white text-xs focus:ring-1 focus:ring-blue-500 shadow-sm outline-none" 
                    placeholder="Search documents...">
            </div>
            <select name="category_id" onchange="this.form.submit()" 
                class="w-36 px-3 py-2 rounded-xl border border-gray-100 bg-white text-xs shadow-sm outline-none cursor-pointer">
                <option value="">Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if(request('category_id') || request('search'))
                <a href="{{ route('documents.index') }}" class="flex items-center px-3 py-2 text-[10px] font-bold text-gray-400 hover:text-red-500 transition-colors uppercase tracking-widest">
                    <i data-lucide="rotate-ccw" class="w-3 h-3 mr-1"></i>
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Simplified Document Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse ($files as $file)
            <div class="bg-white rounded-xl shadow-sm border border-gray-50 overflow-hidden group hover:border-blue-200 transition-all">
                <div class="aspect-square bg-gray-50 flex items-center justify-center relative group-hover:bg-blue-50 transition-colors cursor-pointer" 
                    @click="previewUrl = '{{ route('documents.preview', $file->id) }}'; previewType = '{{ $file->mime_type }}'; fileName = '{{ addslashes($file->display_name) }}'">
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
                        <button @click.stop="previewUrl = '{{ route('documents.preview', $file->id) }}'; previewType = '{{ $file->mime_type }}'; fileName = '{{ addslashes($file->display_name) }}'" 
                            class="p-2 bg-white rounded-lg hover:text-blue-600 transition-colors" title="View">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                        <a href="{{ route('documents.download', $file->id) }}" @click.stop class="p-2 bg-white rounded-lg hover:text-green-600 transition-colors" title="Download">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </a>
                        @if(auth()->user()->role->name === 'root' || $file->user_id === auth()->id())
                            <button @click.stop="openShareModal('{{ $file->id }}', '{{ addslashes($file->display_name) }}')" class="p-2 bg-white rounded-lg hover:text-purple-600 transition-colors" title="Share">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('documents.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Delete?')" @click.stop>
                                @csrf @method('DELETE')
                                <button class="p-2 bg-white rounded-lg hover:text-red-600 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="p-3">
                    <div class="flex justify-between items-start mb-1">
                        @if($file->category)
                            <span class="text-[8px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">{{ $file->category->name }}</span>
                        @endif
                    </div>
                    <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $file->display_name }}">{{ $file->display_name }}</h4>
                    <p class="text-[9px] text-gray-400 mt-0.5">{{ $file->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                <p class="text-xs text-gray-400">No files found.</p>
            </div>
        @endforelse
    </div>

    <!-- Dynamic Preview Overlay -->
    <div x-show="previewUrl" 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md"
         x-transition x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col overflow-hidden" @click.away="closePreview()">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-50 rounded-xl">
                        <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800" x-text="fileName"></h3>
                        <p class="text-[10px] text-gray-400 font-medium">Document Preview</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" download class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Download">
                        <i data-lucide="download" class="w-5 h-5"></i>
                    </a>
                    <button @click="closePreview()" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-gray-100 relative overflow-hidden">
                <template x-if="previewUrl">
                    <iframe :src="previewUrl" class="w-full h-full border-none bg-white" @load="$el.classList.remove('opacity-0')"></iframe>
                </template>
                <div class="absolute inset-0 flex items-center justify-center -z-10">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8">
        {{ $files->links() }}
    </div>

    <!-- Drag Overlay -->
    <div x-show="isDragging" 
         x-transition.opacity
         class="fixed inset-0 z-[100] bg-blue-900/90 backdrop-blur-md flex flex-col items-center justify-center border-[12px] border-blue-500 border-dashed"
         x-cloak>
        <div class="w-32 h-32 bg-white/10 rounded-full flex items-center justify-center mb-6 animate-bounce">
            <i data-lucide="upload-cloud" class="w-16 h-16 text-white text-blue-100"></i>
        </div>
        <h2 class="text-4xl font-extrabold text-white tracking-tight mb-2">Drop it here!</h2>
        <p class="text-blue-200 text-lg font-medium">Release your file to upload into All Documents</p>
    </div>

    <!-- Context Menu -->
    <div x-show="contextMenuOpen" @click.away="contextMenuOpen = false" x-ref="ctxMenu"
         class="fixed z-[90] w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-1"
         :style="`left: ${contextMenuX}px; top: ${contextMenuY}px;`"
         x-transition.opacity.duration.150ms
         x-cloak>
        <button @click="triggerUploadDialog()" class="w-full text-left px-4 py-2.5 text-xs text-blue-700 hover:bg-blue-50 flex items-center font-bold">
            <i data-lucide="upload-cloud" class="w-4 h-4 mr-3 text-blue-500"></i> Upload File
        </button>
    </div>

    @include('share.modal')
</div>
@endsection
