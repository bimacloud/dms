<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $share->document->title }} - Shared Document</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900 min-h-screen flex flex-col md:flex-row">

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
    <main class="flex-1 flex flex-col p-4 md:p-6 lg:p-8 bg-gray-50/50 overflow-hidden h-screen">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 flex-1 overflow-hidden relative group">
            <div class="absolute inset-0 bg-gray-50/80 flex flex-col items-center justify-center z-0">
                <div class="p-4 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center">
                    <i data-lucide="loader-2" class="w-8 h-8 text-blue-500 animate-spin mb-3"></i>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Loading Document</p>
                </div>
            </div>
            
            <iframe src="{{ route('share.preview', $share->token) }}" class="w-full h-full border-none relative z-10 bg-white" onload="this.style.opacity=1" style="opacity:0; transition: opacity 0.5s ease-in-out"></iframe>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
