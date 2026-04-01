@foreach ($items as $item)
    @php
        $hasChildren = $item->relationLoaded('children') && $item->children->isNotEmpty();
        
        // Check if current item or any of its children match current route
        // This ensures parents stay open if a child is active
        $isCurrentActive = $item->route && (request()->routeIs($item->route) || request()->routeIs($item->route . '.*'));
        
        $isChildActive = false;
        if ($hasChildren) {
            // Traverse children to see if any are active
            $isChildActive = $item->children->contains(function($child) {
                return $child->route && (request()->routeIs($child->route) || request()->routeIs($child->route . '.*'));
            });
        }

        $isActive = $isCurrentActive || $isChildActive;
    @endphp
    @php
        $shouldSkip = (auth()->user()->role->name === 'member' && str_contains(strtolower($item->name), 'categor'));
    @endphp

    @if(!$shouldSkip)
    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="relative">
        @if ($hasChildren)
            <button @click="open = !open" 
                class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold rounded-xl transition-all duration-300
                {{ $isActive ? 'bg-slate-800/50 text-white border border-slate-700/50 shadow-sm' : 'text-slate-400 hover:bg-slate-800/30 hover:text-white' }}">
                <div class="flex items-center">
                    <i data-lucide="{{ $item->icon ?: 'circle' }}" class="w-4 h-4 mr-3 {{ $isActive ? 'text-blue-400' : 'text-slate-500' }}"></i>
                    <span x-show="sidebarOpen" x-cloak>{{ $item->name }}</span>
                </div>
                <i x-show="sidebarOpen" data-lucide="chevron-down" 
                    :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" 
                    class="w-3 h-3 transition-transform" x-cloak></i>
            </button>
            <div x-show="open && sidebarOpen" class="mt-1 space-y-1 ml-4 border-l border-slate-800 pl-2" x-transition x-cloak>
                @include('partials.sidebar-items', ['items' => $item->children])
            </div>
        @else
            <a href="{{ $item->route ? route($item->route) : '#' }}" 
                class="flex items-center px-3 py-2.5 text-xs font-bold rounded-xl transition-all duration-300 group
                {{ $isActive ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20 shadow-[0_0_20px_rgba(59,130,246,0.1)] active-menu-glow' : 'text-slate-400 hover:bg-slate-800/30 hover:text-white' }}">
                <i data-lucide="{{ $item->icon ?: 'circle' }}" class="w-4 h-4 mr-3 {{ $isActive ? 'text-blue-400' : 'text-slate-500 group-hover:text-white' }}"></i>
                <span x-show="sidebarOpen" x-cloak>{{ $item->name }}</span>
            </a>
        @endif
    </div>
    @endif
@endforeach