@extends('layouts.app')

@section('header', 'Categories')

@section('content')
<div x-data="{ 
    openModal: false, 
    editMode: false, 
    category: { id: '', name: '', description: '' },
    search: {{ Js::from(request('search')) }},
    submitSearch() {
        const url = new URL(window.location.href);
        url.searchParams.set('search', this.search);
        window.location.href = url.toString();
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
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" x-model="search" @keyup.enter="submitSearch()"
                    class="block w-full pl-11 pr-4 py-3 border border-gray-100 rounded-2xl bg-gray-50/50 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" 
                    placeholder="Search by name or description...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Category Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Description</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Docs</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($categories as $cat)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                                        <i data-lucide="folder" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ $cat->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm text-gray-500">{{ Str::limit($cat->description ?: '-', 50) }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm border border-blue-100/50">
                                    {{ $cat->files_count }}
                                </a>
                            </td>
                            <td class="px-6 py-5 text-right space-x-2">
                                <button @click="editMode = true; category = { id: '{{ $cat->id }}', name: '{{ addslashes($cat->name) }}', description: '{{ addslashes($cat->description) }}' }; openModal = true" 
                                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors inline-block" title="Edit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                @if(auth()->user()->role->name === 'root')
                                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('Archive this category?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
                                <i data-lucide="database" class="w-12 h-12 text-gray-100 mx-auto mb-4"></i>
                                <p class="text-gray-400 text-sm font-medium">No categories matching your filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $categories->links() }}
            </div>
        @endif
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
