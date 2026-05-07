@extends('layouts.app')

@section('header', 'Semua Dokumen')

@section('content')
<div x-data="{ 
    showUpload: false, 
    showPreviewModal: false,
    previewUrl: '', 
    previewMimeType: '',
    previewName: '',
    zoomLevel: 100,
    isMaximized: false,
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
        this.previewMimeType = mimeType;
        this.zoomLevel = 100;
        this.isMaximized = false;
        this.showPreviewModal = true;
        setTimeout(() => lucide.createIcons(), 50);
    },

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
            this.fileName = files.length + ' File Dipilih';
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
        setTimeout(() => lucide.createIcons(), 50);
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
    }
}"
@click="contextMenuOpen = false"
@contextmenu.prevent="if($event.target.closest('.group') === null && $event.target.closest('.bg-white') === null) showContextMenu($event)"
@dragover.prevent="isDragging = true"
@dragleave.prevent="isDragging = false"
@drop.prevent="handleGlobalDrop($event)"
class="relative min-h-screen max-w-7xl mx-auto">

    <!-- Compact Header -->
    <div class="flex items-center justify-between mb-8 bg-white/50 backdrop-blur-md p-6 rounded-[2.5rem] border border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Galeri Dokumen</h1>
            <p class="text-xs font-bold text-gray-400 mt-1 uppercase tracking-widest">Kelola dan atur file digital Anda.</p>
        </div>
        <button @click="showUpload = !showUpload" 
            class="flex items-center px-6 py-3 text-xs font-bold text-white rounded-2xl transition-all shadow-xl shadow-blue-500/20 active:scale-95"
            :class="showUpload ? 'bg-gray-800 hover:bg-gray-700' : 'bg-blue-600 hover:bg-blue-500'">
            <i :data-lucide="showUpload ? 'chevron-up' : 'plus'" class="w-4 h-4 mr-2"></i>
            <span x-text="showUpload ? 'Tutup' : 'Tambah Dokumen'"></span>
        </button>
    </div>

    <!-- Simple Inline Uploader -->
    <div x-show="showUpload" x-transition class="mb-10" x-cloak>
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" @submit="isUploading = true" class="p-8">
                @csrf
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Drop Zone -->
                    <div class="lg:w-1/3 relative group min-h-[180px] rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 hover:bg-blue-50 transition-all flex items-center justify-center cursor-pointer overflow-hidden p-6">
                        <template x-if="!filePreview">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="upload-cloud" class="w-6 h-6 text-blue-600"></i>
                                </div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tarik file atau klik</p>
                            </div>
                        </template>
                        <template x-if="filePreview">
                            <div class="text-center relative z-10">
                                <template x-if="filePreview === 'pdf'">
                                    <i data-lucide="file-text" class="w-12 h-12 text-red-500 mx-auto"></i>
                                </template>
                                <template x-if="filePreview === 'multiple'">
                                    <i data-lucide="files" class="w-12 h-12 text-indigo-500 mx-auto"></i>
                                </template>
                                <template x-if="filePreview !== 'pdf' && filePreview !== 'multiple'">
                                    <img :src="filePreview" class="w-24 h-20 object-cover rounded-xl shadow-lg mx-auto">
                                </template>
                                <p class="text-[10px] font-bold text-gray-600 mt-3 truncate max-w-[150px]" x-text="fileName"></p>
                                <button type="button" @click="filePreview = null; fileName = ''; document.getElementById('globalFileInput').value = ''" class="mt-2 text-[10px] font-black text-red-500 hover:underline uppercase tracking-tighter">Ganti File</button>
                            </div>
                        </template>
                        <input type="file" name="files[]" id="globalFileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required multiple @change="handleFile">
                    </div>

                    <!-- Inputs & Action -->
                    <div class="lg:flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2" x-show="filePreview !== 'multiple'">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama File</label>
                            <input type="text" name="display_name" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm font-bold focus:bg-white focus:border-blue-400 border outline-none transition-all shadow-inner" :required="filePreview !== 'multiple'" placeholder="Contoh: Invoice #123">
                        </div>
                        <div class="space-y-2" :class="{'md:col-span-2': filePreview === 'multiple'}">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Kategori</label>
                            <select name="category_id" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm font-bold focus:bg-white focus:border-blue-400 border outline-none transition-all cursor-pointer shadow-inner appearance-none">
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 pt-6 flex items-center justify-between border-t border-gray-50 mt-2">
                            <div x-show="isUploading" class="flex-1 mr-6 bg-gray-100 rounded-full h-2 overflow-hidden" x-cloak>
                                <div class="bg-blue-600 h-full animate-pulse w-full"></div>
                            </div>
                            <button type="submit" 
                                class="ml-auto flex items-center bg-blue-600 text-white px-8 py-3.5 rounded-2xl text-xs font-black hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95 disabled:opacity-50"
                                :disabled="isUploading">
                                <i data-lucide="check" class="w-4 h-4 mr-2" x-show="!isUploading"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="isUploading" x-cloak></i>
                                <span x-text="isUploading ? 'Sedang Mengunggah...' : 'Simpan Dokumen'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-4 mb-8">
        <form action="{{ route('documents.index') }}" method="GET" class="flex flex-1 gap-3 max-w-2xl bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-4 top-3 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border-none bg-gray-50/50 text-xs font-bold focus:bg-white focus:ring-0 outline-none" 
                    placeholder="Cari dokumen Anda...">
            </div>
            <select name="category_id" onchange="this.form.submit()" 
                class="w-44 px-4 py-2.5 rounded-xl border-none bg-gray-50/50 text-xs font-bold outline-none cursor-pointer focus:bg-white transition-all appearance-none">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if(request('category_id') || request('search'))
                <a href="{{ route('documents.index') }}" class="flex items-center px-4 py-2.5 text-[10px] font-black text-gray-400 hover:text-red-500 transition-colors uppercase tracking-widest">
                    <i data-lucide="rotate-ccw" class="w-3 h-3 mr-2"></i>
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Document Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @forelse ($files as $file)
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden group hover:border-blue-300 hover:shadow-2xl hover:shadow-blue-500/10 transition-all flex flex-col cursor-pointer relative"
                 @click="openPreviewModal('{{ route('documents.preview', $file->id) }}', '{{ addslashes($file->display_name) }}', '{{ $file->mime_type }}')">
                <div class="aspect-square bg-gray-50 flex items-center justify-center relative group-hover:bg-blue-50 transition-colors">
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
                        <button @click.stop="openPreviewModal('{{ route('documents.preview', $file->id) }}', '{{ addslashes($file->display_name) }}', '{{ $file->mime_type }}')" 
                            class="w-10 h-10 bg-white text-gray-700 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-lg flex items-center justify-center" title="Lihat">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                        <a href="{{ route('documents.download', $file->id) }}" @click.stop class="w-10 h-10 bg-white text-gray-700 rounded-xl hover:bg-green-600 hover:text-white transition-all shadow-lg flex items-center justify-center" title="Unduh">
                            <i data-lucide="download" class="w-5 h-5"></i>
                        </a>
                        @if(auth()->user()->role->name === 'root' || $file->user_id === auth()->id())
                            <button @click.stop="openShareModal('{{ $file->id }}', '{{ addslashes($file->display_name) }}')" class="w-10 h-10 bg-white text-gray-700 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-lg flex items-center justify-center" title="Bagikan">
                                <i data-lucide="share-2" class="w-5 h-5"></i>
                            </button>
                            <form action="{{ route('documents.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')" @click.stop>
                                @csrf @method('DELETE')
                                <button class="w-10 h-10 bg-white text-gray-700 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-lg flex items-center justify-center" title="Hapus">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="p-5 bg-white">
                    <div class="flex justify-between items-start mb-2">
                        @if($file->category)
                            <span class="text-[8px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg uppercase tracking-tighter">{{ $file->category->name }}</span>
                        @endif
                    </div>
                    <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $file->display_name }}">{{ $file->display_name }}</h4>
                    <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-tighter">{{ $file->created_at->translatedFormat('d M Y') }}</p>
                </div>
            </div>
        @empty
            <div class="col-span-full py-24 text-center bg-white rounded-[3rem] border border-dashed border-gray-200 shadow-sm">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Dokumen Tidak Ditemukan</h3>
                <p class="text-sm text-gray-400 mt-2">Coba gunakan kata kunci pencarian yang berbeda.</p>
            </div>
        @endforelse
    </div>

    <!-- Premium Preview Modal -->
    <div x-show="showPreviewModal" 
         class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/95 backdrop-blur-3xl transition-all duration-500"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-105" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-105"
         x-cloak>
        
        <!-- Header Toolbar -->
        <div class="absolute top-0 left-0 right-0 p-6 flex items-center justify-between z-[210] bg-gradient-to-b from-black/50 to-transparent">
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
                    <!-- Image Preview -->
                    <template x-if="previewMimeType.startsWith('image/')">
                        <div class="relative group cursor-zoom-out" @click="showPreviewModal = false">
                            <img :src="previewUrl" 
                                 :style="`transform: scale(${zoomLevel/100});`"
                                 class="max-w-full max-h-[75vh] rounded-2xl shadow-[0_0_100px_rgba(0,0,0,0.5)] border border-white/10 transition-transform duration-300">
                        </div>
                    </template>

                    <!-- PDF / Document Preview -->
                    <template x-if="previewMimeType === 'application/pdf'">
                        <div class="w-full max-w-5xl h-full bg-white rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/20">
                            <iframe :src="previewUrl" class="w-full h-full border-none"></iframe>
                        </div>
                    </template>

                    <!-- Video Preview -->
                    <template x-if="previewMimeType.startsWith('video/')">
                        <video controls class="max-w-full max-h-[80vh] rounded-[2.5rem] shadow-2xl border border-white/20" :src="previewUrl" autoplay></video>
                    </template>

                    <!-- Audio Preview -->
                    <template x-if="previewMimeType.startsWith('audio/')">
                        <div class="bg-white/10 backdrop-blur-2xl p-12 rounded-[3rem] border border-white/20 text-center">
                            <div class="w-24 h-24 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-8">
                                <i data-lucide="music" class="w-10 h-10 text-blue-400"></i>
                            </div>
                            <audio controls class="w-80" :src="previewUrl"></audio>
                        </div>
                    </template>

                    <!-- Fallback -->
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

    <div class="mt-12 mb-8">
        {{ $files->links() }}
    </div>

    <!-- Drag Overlay -->
    <div x-show="isDragging" 
         x-transition.opacity
         class="fixed inset-0 z-[300] bg-blue-900/90 backdrop-blur-md flex flex-col items-center justify-center border-[12px] border-blue-500 border-dashed"
         x-cloak>
        <div class="w-32 h-32 bg-white/10 rounded-full flex items-center justify-center mb-6 animate-bounce">
            <i data-lucide="upload-cloud" class="w-16 h-16 text-white"></i>
        </div>
        <h2 class="text-4xl font-extrabold text-white tracking-tight mb-2">Lepaskan di sini!</h2>
        <p class="text-blue-200 text-lg font-medium">File Anda akan otomatis diunggah ke Galeri Dokumen</p>
    </div>

    <!-- Context Menu -->
    <div x-show="contextMenuOpen" @click.away="contextMenuOpen = false" x-ref="ctxMenu"
         class="fixed z-[150] w-64 bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-100 p-2"
         :style="`left: ${contextMenuX}px; top: ${contextMenuY}px;`"
         x-transition.opacity.duration.150ms
         x-cloak>
        <button @click="triggerUploadDialog()" class="w-full text-left px-5 py-4 text-xs font-black text-blue-600 hover:bg-blue-600 hover:text-white rounded-2xl flex items-center transition-all">
            <i data-lucide="upload-cloud" class="w-4 h-4 mr-3"></i> Unggah File Baru
        </button>
    </div>

    @include('share.modal')
</div>
@endsection
