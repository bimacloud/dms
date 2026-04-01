<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $share->document->title }} - Shared Document</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900 min-h-screen flex flex-col md:flex-row" 
      x-data="{ 
        zoomLevel: 100, 
        isMaximized: false, 
        mimeType: '{{ $share->shareable->mime_type ?? '' }}',
        zoomIn() { if (this.zoomLevel < 300) this.zoomLevel += 25 },
        zoomOut() { if (this.zoomLevel > 25) this.zoomLevel -= 25 },
        toggleMaximize() { this.isMaximized = !this.isMaximized; this.zoomLevel = 100; setTimeout(() => lucide.createIcons(), 10) }
      }">

    <!-- Left Sidebar (Metadata & Actions) -->
    <aside class="w-full md:w-80 lg:w-[400px] bg-white border-b md:border-b-0 md:border-r border-gray-200 flex flex-col p-6 md:p-8 shadow-sm z-20">
        <div class="flex-1">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm border border-blue-100">
                <i data-lucide="file-text" class="w-8 h-8"></i>
            </div>
            
            <h1 class="font-bold text-gray-900 text-xl md:text-2xl leading-tight mb-3">{{ $share->document->title }}</h1>
            
            <div class="space-y-4 mt-8 bg-gray-50 rounded-xl p-5 border border-gray-100">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Shared By</p>
                    <div class="flex items-center text-sm font-bold text-gray-700">
                        <i data-lucide="user" class="w-4 h-4 mr-2 text-blue-500"></i>
                        {{ $share->creator->name }}
                    </div>
                </div>
                
                @if($share->expired_at)
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Expires On</p>
                    <div class="flex items-center text-sm font-bold text-gray-700">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2 text-red-500"></i>
                        {{ $share->expired_at->format('M d, Y H:i') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="pt-6 mt-6 border-t border-gray-100">
            <form action="{{ route('download.generate.public') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $share->token }}">
                <button type="submit" class="w-full flex items-center justify-center px-4 py-3.5 bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                    <i data-lucide="download-cloud" class="w-5 h-5 mr-2"></i>
                    Download Securely
                </button>
            </form>
            <p class="text-[10px] text-center text-gray-400 mt-4 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-3 h-3 mr-1 text-green-500"></i>
                End-to-End Encrypted Transfer
            </p>
        </div>
    </aside>

    <!-- Right Content (Preview) -->
    <main class="flex-1 flex flex-col p-4 md:p-6 lg:p-8 bg-gray-900 overflow-hidden h-screen relative">
        
        <!-- Floating Glass Toolbar (Only for Images) -->
        <template x-if="mimeType.startsWith('image/')">
            <div class="absolute top-8 left-1/2 -translate-x-1/2 z-[110] flex items-center gap-4 px-6 py-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl transition-all group">
                <div class="flex items-center gap-1">
                    <button @click="zoomOut()" class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-xl transition-all" title="Zoom Out">
                        <i data-lucide="zoom-out" class="w-4 h-4"></i>
                    </button>
                    <span class="text-[10px] font-mono font-bold text-blue-400 w-12 text-center" x-text="zoomLevel + '%'"></span>
                    <button @click="zoomIn()" class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-xl transition-all" title="Zoom In">
                        <i data-lucide="zoom-in" class="w-4 h-4"></i>
                    </button>
                    <div class="w-px h-4 bg-white/10 mx-1"></div>
                    <button @click="toggleMaximize()" class="p-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-xl transition-all" :title="isMaximized ? 'Fit to Screen' : 'Actual Size'">
                        <i :data-lucide="isMaximized ? 'minimize-2' : 'maximize-2'" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </template>

        <div class="w-full h-full flex items-center justify-center relative">
            <div class="absolute inset-0 bg-black/20 flex flex-col items-center justify-center z-0">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Loading...</p>
                </div>
            </div>
            
            <!-- Image Preview -->
            <template x-if="mimeType.startsWith('image/')">
                <div class="w-full h-full overflow-auto flex items-center justify-center p-8 z-10 scrollbar-hide">
                    <img src="{{ route('share.preview', $share->token) }}" 
                         :style="isMaximized ? `width: auto; max-width: none; transform: scale(${zoomLevel/100}); cursor: zoom-out;` : 'max-width: 95%; max-height: 95%; object-fit: contain; cursor: zoom-in;'"
                         class="transition-all duration-300 shadow-[0_0_80px_rgba(0,0,0,0.6)] rounded-lg bg-gray-800"
                         @click="toggleMaximize()"
                         onload="this.style.opacity=1" style="opacity:0">
                </div>
            </template>

            <!-- Iframe Preview -->
            <template x-if="!mimeType.startsWith('image/')">
                <div class="w-full max-w-5xl h-[85vh] bg-white rounded-3xl overflow-hidden shadow-2xl z-10">
                    <iframe src="{{ route('share.preview', $share->token) }}" class="w-full h-full border-none" onload="this.style.opacity=1" style="opacity:0; transition: opacity 0.5s ease-in-out"></iframe>
                </div>
            </template>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
