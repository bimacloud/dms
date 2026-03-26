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

    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
        @if ($hasChildren)
            <button @click="open = !open" 
                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md transition-colors
                {{ $isActive ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <div class="flex items-center">
                    <i data-lucide="{{ $item->icon ?: 'circle' }}" class="w-5 h-5 mr-3"></i>
                    <span x-show="sidebarOpen" x-cloak>{{ $item->name }}</span>
                </div>
                <i x-show="sidebarOpen" data-lucide="chevron-down" 
                    :class="open ? 'rotate-180' : ''" 
                    class="w-4 h-4 transition-transform" x-cloak></i>
            </button>
            <div x-show="open && sidebarOpen" class="mt-1 space-y-1 ml-4" x-cloak>
                @include('partials.sidebar-items', ['items' => $item->children])
            </div>
        @else
            <a href="{{ $item->route ? route($item->route) : '#' }}" 
                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors
                {{ $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <i data-lucide="{{ $item->icon ?: 'circle' }}" class="w-5 h-5 mr-3"></i>
                <span x-show="sidebarOpen" x-cloak>{{ $item->name }}</span>
            </a>
        @endif
    </div>
@endforeach