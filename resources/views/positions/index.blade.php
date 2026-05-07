@extends('layouts.app')

@section('header', 'Job Positions')

@section('content')
<div x-data="{ 
    openModal: false, 
    editMode: false, 
    position: { id: '', name: '' },
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
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Job Positions</h1>
            <p class="text-sm text-gray-500 mt-1 uppercase tracking-tighter font-bold">Manage employee positions</p>
        </div>
        <button @click="editMode = false; position = { id: '', name: '' }; openModal = true"
            class="flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-blue-200 hover:bg-blue-500 transition-all active:scale-95">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Position
        </button>
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
                    placeholder="Search positions...">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
            @forelse ($positions as $pos)
                <div class="group relative bg-white rounded-[2rem] border border-gray-100 shadow-xl shadow-gray-100/50 hover:shadow-2xl hover:shadow-gray-200/60 transition-all duration-500 hover:-translate-y-2 overflow-hidden flex flex-col h-full">
                    <!-- Top Accent -->
                    <div class="h-16 w-full bg-gradient-to-r from-slate-800 to-slate-900 relative overflow-hidden flex items-center px-6">
                         <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white backdrop-blur-sm mr-3">
                             <i data-lucide="briefcase" class="w-5 h-5"></i>
                         </div>
                         <h3 class="text-white font-bold truncate">{{ $pos->name }}</h3>
                    </div>

                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center text-gray-500 text-sm mb-4 font-medium">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mr-3">
                                <i data-lucide="users" class="w-4 h-4"></i>
                            </div>
                            {{ $pos->users_count }} Users assigned
                        </div>

                        <div class="mt-auto flex items-center justify-end pt-4 border-t border-gray-50 gap-2">
                            <button @click="editMode = true; position = { id: '{{ $pos->id }}', name: '{{ addslashes($pos->name) }}' }; openModal = true" 
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            @if(auth()->user()->role->name === 'root' || auth()->user()->role->name === 'admin')
                                <form action="{{ route('positions.destroy', $pos->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this position?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="briefcase" class="w-10 h-10 text-gray-300"></i>
                    </div>
                    <h3 class="text-gray-900 font-bold mb-1">No Positions Found</h3>
                    <p class="text-gray-400 text-xs">Try adjusting your search criteria or create a new one.</p>
                </div>
            @endforelse
        </div>

        @if($positions->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $positions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    <template x-if="openModal">
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="openModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative z-10 inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight" x-text="editMode ? 'Edit Position' : 'Create Position'"></h3>
                        <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="editMode ? '{{ url('settings/positions') }}/' + position.id : '{{ route('positions.store') }}'" method="POST" class="p-8">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Position Name</label>
                                <input type="text" name="name" x-model="position.name" 
                                    class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none" 
                                    required placeholder="e.g. Manager">
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
                                <span x-text="editMode ? 'Update Position' : 'Save Position'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
