@extends('layouts.app')

@section('header', 'Categories')

@section('content')
<div x-data="{ 
    openModal: false, 
    editMode: false, 
    category: { id: '', name: '', description: '' },
    search: {{ Js::from(request('search')) }} || '',
    isLoading: false,
    debounceTimer: null,
    init() {
        this.$watch('search', value => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.submitSearch();
            }, 300);
        });
    },
    submitSearch() {
        clearTimeout(this.debounceTimer);
        const url = new URL(window.location.href);
        const cleanSearch = this.search ? this.search.trim() : '';
        if (cleanSearch !== '') {
            url.searchParams.set('search', cleanSearch);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page');
        this.loadUrl(url.toString());
    },
    loadUrl(url) {
        this.isLoading = true;
        window.history.pushState({}, '', url);
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newGrid = doc.querySelector('.grid-cols-1');
            const currentGrid = this.$el.querySelector('.grid-cols-1');
            if (newGrid && currentGrid) {
                currentGrid.innerHTML = newGrid.innerHTML;
            }
            
            const newPagination = doc.getElementById('pagination-container');
            const currentPagination = this.$el.querySelector('#pagination-container');
            if (currentPagination && newPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
            }
            
            if (window.lucide) {
                window.lucide.createIcons();
            }
        })
        .catch(err => console.error(err))
        .finally(() => {
            this.isLoading = false;
        });
    },
    handlePaginationClick(e) {
        const link = e.target.closest('a');
        if (link && link.href) {
            e.preventDefault();
            this.loadUrl(link.href);
        }
    }
}">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Categories</h1>
            <p class="text-sm text-gray-500 mt-1 uppercase tracking-tighter font-bold">Organize your document universe</p>
        </div>
        <button @click="editMode = false; category = { id: '', name: '', description: '' }; openModal = true"
            class="flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-blue-200 hover:bg-blue-500 transition-all active:scale-95">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Category
        </button>
    </div>

    <!-- Stats Mini-Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Categories</p>
                    <p class="text-xl font-bold text-gray-900">{{ $categories->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Area -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <!-- Table Header / Actions -->
        <div class="p-6 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <i x-show="!isLoading" data-lucide="search" class="w-4 h-4"></i>
                    <i x-show="isLoading" data-lucide="loader-2" class="w-4 h-4 animate-spin text-blue-500" x-cloak></i>
                </div>
                <input type="text" x-model="search" @keyup.enter="submitSearch()"
                    class="block w-full pl-11 pr-10 py-3 border border-gray-100 rounded-2xl bg-gray-50/50 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" 
                    placeholder="Search by name or description...">
                <button x-show="search && search.trim() !== ''" @click="search = ''; submitSearch();" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    type="button">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6 transition-opacity duration-200" :class="isLoading ? 'opacity-50 pointer-events-none' : ''">
            @forelse ($categories as $cat)
                @php
                    // Generate a consistent gradient based on title
                    $hash = substr(md5($cat->name), 0, 6);
                    $colors = [
                        'blue' => 'from-blue-500 to-indigo-600 shadow-blue-200',
                        'purple' => 'from-purple-500 to-pink-600 shadow-purple-200',
                        'amber' => 'from-amber-400 to-orange-600 shadow-amber-200',
                        'emerald' => 'from-emerald-400 to-teal-600 shadow-emerald-200',
                        'rose' => 'from-rose-400 to-red-600 shadow-rose-200',
                        'indigo' => 'from-indigo-400 to-blue-700 shadow-indigo-200',
                    ];
                    $keys = array_keys($colors);
                    $colorKey = $keys[hexdec(substr($hash, 0, 1)) % count($keys)];
                    $gradient = $colors[$colorKey];
                @endphp
                <div class="group relative bg-white rounded-[2rem] border border-gray-100 shadow-xl shadow-gray-100/50 hover:shadow-2xl hover:shadow-gray-200/60 transition-all duration-500 hover:-translate-y-2 overflow-hidden flex flex-col h-full">
                    <!-- Top Gradient Accent -->
                    <div class="h-24 w-full bg-gradient-to-br {{ $gradient }} relative overflow-hidden">
                        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        
                        <div class="absolute bottom-0 left-0 p-6 flex items-center justify-between w-full">
                            <div class="w-12 h-12 bg-white rounded-2xl shadow-lg flex items-center justify-center transform group-hover:rotate-12 transition-transform duration-500">
                                <i data-lucide="layers" class="w-6 h-6 text-gray-800"></i>
                            </div>
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black text-white uppercase tracking-widest border border-white/30">
                                {{ $cat->files_count }} Docs
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-sm font-black text-gray-900 mb-2 truncate group-hover:text-blue-600 transition-colors">{{ $cat->name }}</h3>
                        <p class="text-[11px] text-gray-500 leading-relaxed mb-6 line-clamp-3">
                            {{ $cat->description ?: 'No description provided for this category.' }}
                        </p>

                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-50">
                            <div class="flex gap-1">
                                <button @click="editMode = true; category = { id: '{{ $cat->id }}', name: '{{ addslashes($cat->name) }}', description: '{{ addslashes($cat->description) }}' }; openModal = true" 
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                @if(auth()->user()->role->name === 'root')
                                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('Archive this category?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Archive">
                                            <i data-lucide="archive" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" 
                               class="flex items-center gap-2 text-[10px] font-black text-blue-600 uppercase tracking-widest hover:translate-x-1 transition-transform">
                                View Items
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
                    </div>
                    <h3 class="text-gray-900 font-bold mb-1">No Categories Found</h3>
                    <p class="text-gray-400 text-xs">Try adjusting your search criteria or create a new one.</p>
                </div>
            @endforelse
        </div>

        <div id="pagination-container" @click="handlePaginationClick($event)">
            @if($categories->hasPages())
                <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Premium Modal -->
    <template x-if="openModal">
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="openModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative z-10 inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight" x-text="editMode ? 'Edit Category' : 'Create Category'"></h3>
                        <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="editMode ? '{{ url('categories') }}/' + category.id : '{{ route('categories.store') }}'" method="POST" class="p-8">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Category Name</label>
                                <input type="text" name="name" x-model="category.name" 
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none" 
                                    required placeholder="e.g. Legal Documents">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Description (Optional)</label>
                                <textarea name="description" x-model="category.description" 
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none" 
                                    rows="4" placeholder="Briefly describe what goes into this category..."></textarea>
                            </div>
                        </div>

                        <div class="mt-10 flex items-center justify-end gap-3">
                            <button type="button" @click="openModal = false" 
                                class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="px-8 py-3 rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95 flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span x-text="editMode ? 'Update Category' : 'Save Category'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
