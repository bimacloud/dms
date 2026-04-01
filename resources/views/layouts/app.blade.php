<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Modern DMS') }}</title>
    <!-- Tailwind CSS 4 CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
        
        .active-menu-glow {
            position: relative;
        }
        .active-menu-glow::after {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 16px;
            background: #3b82f6;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 10px #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">

    <!-- Mobile Sidebar -->
    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" x-cloak>
        <!-- Backdrop -->
        <div x-show="mobileSidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="mobileSidebarOpen = false"></div>

        <!-- Sidebar Content -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative flex flex-col w-full max-w-xs h-full bg-gray-900 shadow-2xl">
            
            <div class="px-6 py-5 border-b border-gray-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="folder-key" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-white tracking-tight">DMS App</span>
                </div>
                <button @click="mobileSidebarOpen = false" class="p-2 -mr-2 text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar" x-data="{ sidebarOpen: true }">
                @include('partials.sidebar-items', ['items' => $menus])
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center p-3 bg-gray-800/50 rounded-xl">
                    <div class="h-10 w-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 font-bold border border-blue-500/30">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-bold text-white leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-gray-400 uppercase mt-1 tracking-wider">{{ auth()->user()->role->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div :class="sidebarOpen ? 'w-64' : 'w-20'" class="hidden lg:flex flex-col fixed inset-y-0 bg-[#0f172a] transition-all duration-300 z-30 overflow-hidden border-r border-slate-800 shadow-[20px_0_50px_rgba(0,0,0,0.1)]">
        <div class="p-6 border-b border-slate-800/50 flex items-center justify-between h-16 bg-[#0f172a]/50 backdrop-blur-xl sticky top-0 z-10">
            <div class="flex items-center space-x-2" x-show="sidebarOpen" x-transition>
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-900/20">
                    <i data-lucide="folder-key" class="w-5 h-5 text-white"></i>
                </div>
                <span class="text-xl font-bold text-white tracking-tight" x-cloak>DMS App</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white transition-all transform hover:scale-110" :class="!sidebarOpen ? 'mx-auto' : ''">
                <i :data-lucide="sidebarOpen ? 'chevron-left' : 'menu'" class="w-6 h-6"></i>
            </button>
        </div>
        <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto custom-scrollbar">
            @include('partials.sidebar-items', ['items' => $menus])
        </nav>

        <!-- User Profile Area -->
        <div class="p-4 border-t border-slate-800/50 bg-[#0f172a]/80 backdrop-blur-md">
            <div class="flex items-center" :class="sidebarOpen ? 'p-3 bg-slate-800/30 rounded-2xl border border-slate-700/30' : 'justify-center p-2'" title="{{ auth()->user()->name }} ({{ auth()->user()->role->name }})">
                <div class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center font-bold border {{ auth()->user()->role->name === 'root' ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30' }}">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="ml-3 overflow-hidden transition-all duration-300" x-show="sidebarOpen" x-transition x-cloak>
                    <p class="text-sm font-bold text-white leading-none truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] uppercase mt-1 tracking-wider font-bold flex items-center {{ auth()->user()->role->name === 'root' ? 'text-red-400' : (auth()->user()->role->name === 'admin' ? 'text-blue-400' : 'text-gray-400') }}">
                        @if(auth()->user()->role->name === 'root')
                            <i data-lucide="shield-alert" class="w-3 h-3 mr-1"></i>
                        @endif
                        {{ auth()->user()->role->name }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" class="flex flex-col flex-1 transition-all duration-300 min-h-screen">
        <!-- Topbar -->
        <header class="bg-white/70 backdrop-blur-2xl border-b border-slate-100 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-20 shadow-sm shadow-slate-200/20">
            <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-500 hover:bg-slate-100 rounded-xl transition-all">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            
            <div class="flex-1 flex justify-between items-center ml-2 lg:ml-0">
                <h2 class="text-lg font-bold text-gray-900 tracking-tight">@yield('header', 'Dashboard')</h2>
                
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Dynamic Header Menus -->
                    <div class="hidden md:flex items-center space-x-1 border-r border-gray-100 pr-4 mr-2">
                        @foreach($headerMenus as $hm)
                            <a href="{{ $hm->route ? route($hm->route) : '#' }}" 
                                class="flex items-center px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all gap-2"
                                title="{{ $hm->name }}">
                                <i data-lucide="{{ $hm->icon ?: 'circle' }}" class="w-4 h-4"></i>
                                <span class="hidden xl:inline">{{ $hm->name }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-gray-900 truncate max-w-[120px]">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-tighter flex items-center justify-end {{ auth()->user()->role->name === 'root' ? 'text-red-600' : 'text-blue-600' }}">
                            @if(auth()->user()->role->name === 'root')
                                <i data-lucide="shield-alert" class="w-3 h-3 mr-0.5"></i>
                            @endif
                            {{ auth()->user()->role->name }}
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 text-green-700" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
