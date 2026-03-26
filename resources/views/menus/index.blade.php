@extends('layouts.app')

@section('header', 'Menu Management')

@section('content')
<div x-data="{ 
    showForm: false, 
    editMode: false, 
    searchQuery: '',
    menu: { id: '', name: '', route: '', icon: '', parent_id: '', position: 'sidebar', order: 0, is_active: true, roles: [] },
    resetForm() {
        this.menu = { id: '', name: '', route: '', icon: '', parent_id: '', position: 'sidebar', order: 0, is_active: true, roles: [] };
        this.editMode = false;
        this.showForm = false;
    },
    editMenu(m) {
        this.menu = { ...m };
        this.editMode = true;
        this.showForm = true;
        if (window.innerWidth >= 1024) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },
    fullMenus: {{ Js::from($menusData) }},
    isRoot: {{ auth()->user()->role->name === 'root' ? 'true' : 'false' }},
    get filteredMenus() {
        if (!this.searchQuery) return this.fullMenus;
        return this.fullMenus.filter(m => 
            m.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            m.position.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    }
}" x-init="
    $nextTick(() => { lucide.createIcons() });
    $watch('searchQuery', () => { $nextTick(() => lucide.createIcons()) });
    $watch('showForm', () => { $nextTick(() => lucide.createIcons()) });
">
    <!-- Premium Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Navigation System</h1>
            <p class="text-sm text-gray-500 mt-1 uppercase tracking-tighter font-bold">Architecture of your workspace</p>
        </div>
        @if(auth()->user()->role->name === 'root')
            <button @click="if(showForm && !editMode) { showForm = false } else { resetForm(); showForm = true }"
                class="flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-xl transition-all active:scale-95"
                :class="showForm && !editMode ? 'bg-gray-800 hover:bg-gray-700' : 'bg-blue-600 hover:bg-blue-500 shadow-blue-200'">
                <i :data-lucide="showForm && !editMode ? 'x' : 'plus'" class="w-4 h-4 mr-2"></i>
                <span x-text="showForm && !editMode ? 'Cancel' : 'New Menu Item'"></span>
            </button>
        @endif
    </div>

    <!-- Main Split Layout -->
    <div class="flex flex-col lg:flex-row gap-8 items-start relative min-h-[600px]">
        
        <!-- Menu DataTable (Left/Center) -->
        <div :class="showForm ? 'lg:w-[65%]' : 'w-full'" class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden transition-all duration-500 ease-in-out">
            <!-- Search & Filter Area -->
            <div class="p-6 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" x-model="searchQuery"
                        class="block w-full pl-11 pr-4 py-3 border border-gray-100 rounded-2xl bg-gray-50/50 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" 
                        placeholder="Search menu labels...">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Label & Structure</th>
                            <th :class="showForm ? 'hidden xl:table-cell' : ''" class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Configuration</th>
                            <th :class="showForm ? 'hidden' : ''" class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Visibility</th>
                            <th :class="showForm ? 'hidden' : ''" class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="m in filteredMenus" :key="m.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div x-show="m.parent_id" class="mr-2 pl-2 border-l-2 border-gray-100 text-gray-200">
                                            <i data-lucide="corner-down-right" class="w-3 h-3"></i>
                                        </div>
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-100 group-hover:text-blue-600 transition-all border border-gray-100 group-hover:border-blue-200">
                                            <i :data-lucide="m.icon || 'circle'" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900" x-text="m.name"></div>
                                            <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest" x-text="'ORDER: ' + m.order"></div>
                                        </div>
                                    </div>
                                </td>
                                <td :class="showForm ? 'hidden xl:table-cell' : ''" class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100/50 self-start text-nowrap" x-text="m.route || 'STATIC SECTION'"></span>
                                        <span :class="m.position === 'sidebar' ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-purple-50 text-purple-600 border-purple-100'" 
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border self-start" x-text="m.position === 'sidebar' ? 'SIDEBAR' : 'HEADER'">
                                        </span>
                                    </div>
                                </td>
                                <td :class="showForm ? 'hidden' : ''" class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                                        <template x-for="rn in m.role_names">
                                            <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-0.5 text-[9px] font-bold text-gray-600 border border-gray-200 uppercase tracking-tighter capitalize" x-text="rn"></span>
                                        </template>
                                    </div>
                                </td>
                                <td :class="showForm ? 'hidden' : ''" class="px-6 py-4 text-center">
                                    <span :class="m.is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'" 
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-current/10">
                                        <span x-text="m.is_active ? 'ACTIVE' : 'ARCHIVED'"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button @click="editMenu(m)" 
                                            class="p-2 text-gray-400 hover:text-blue-600 transition-all rounded-xl hover:bg-white shadow-sm hover:shadow-gray-200" title="Modify Architecture">
                                            <i data-lucide="settings-2" class="w-4 h-4"></i>
                                        </button>
                                        <template x-if="isRoot">
                                            <form :action="'{{ url('settings/menus') }}/' + m.id" method="POST" class="inline" onsubmit="return confirm('Dismantle this navigation item?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition-all rounded-xl hover:bg-white shadow-sm hover:shadow-gray-200" title="Remove Item">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Side Panel / COMPACT Mobile Modal Form -->
        <div x-show="showForm" 
             class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-gray-900/40 backdrop-blur-sm lg:relative lg:inset-auto lg:z-10 lg:flex lg:items-start lg:p-0 lg:bg-transparent lg:backdrop-blur-none lg:w-[35%] lg:sticky lg:top-24 h-full lg:h-fit transition-all duration-300" 
             x-cloak>
            
            <!-- Backdrop (Mobile only) -->
            <div class="absolute inset-0 lg:hidden" @click="resetForm()"></div>

            <div x-show="showForm" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-12 lg:translate-y-0 lg:translate-x-12"
                 x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 lg:translate-x-0"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-12 lg:translate-y-0 lg:translate-x-12"
                 class="relative bg-white rounded-t-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden w-full max-w-xl lg:max-w-none max-h-[85vh] lg:max-h-none overflow-y-auto custom-scrollbar">
                
                <div class="bg-blue-600 px-6 py-4 flex items-center justify-between text-white sticky top-0 z-10 shadow-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 backdrop-blur-md rounded-lg flex items-center justify-center">
                            <i :data-lucide="editMode ? 'edit-3' : 'plus-circle'" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest" x-text="editMode ? 'Update Menu' : 'New Menu'"></h3>
                            <p class="text-[9px] text-white/60 font-medium tracking-tight">Compact Configuration</p>
                        </div>
                    </div>
                    <button @click="resetForm()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 transition-all">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form :action="editMode ? '{{ url('settings/menus') }}/' + menu.id : '{{ route('menus.store') }}'" method="POST" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">

                    <!-- Error Feedback -->
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded-lg flex items-start gap-3">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 mt-0.5"></i>
                            <div class="text-[10px] text-red-700 font-bold uppercase tracking-tight">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div class="space-y-1.5 text-left">
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Display Label</label>
                            <input type="text" name="name" x-model="menu.name" 
                                class="w-full rounded-xl border-gray-100 bg-gray-50/50 p-3.5 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-bold" 
                                required placeholder="e.g. Analytics">
                        </div>
                        
                        <div class="space-y-1.5 text-left">
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Route Alias</label>
                            <input type="text" name="route" x-model="menu.route" 
                                class="w-full rounded-xl border-gray-100 bg-gray-50/50 p-3.5 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium" 
                                placeholder="e.g. reports.index">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5 text-left">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Icon</label>
                                <div class="relative">
                                    <input type="text" name="icon" x-model="menu.icon" 
                                        class="w-full rounded-xl border-gray-100 bg-gray-50/50 p-3.5 pl-10 text-xs focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-blue-600">
                                        <i :data-lucide="menu.icon || 'circle'" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-1.5 text-left">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Order</label>
                                <input type="number" name="order" x-model="menu.order" 
                                    class="w-full rounded-xl border-gray-100 bg-gray-50/50 p-3.5 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-left">
                            <div class="space-y-1.5">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Parent</label>
                                <select name="parent_id" x-model="menu.parent_id" 
                                    class="w-full rounded-xl border-gray-100 bg-gray-50/50 p-3.5 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none cursor-pointer font-medium">
                                    <option value="">None</option>
                                    @foreach ($parentMenus as $pm)
                                        <template x-if="menu.id != {{ $pm->id }}">
                                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                        </template>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Position</label>
                                <select name="position" x-model="menu.position" 
                                    class="w-full rounded-xl border-gray-100 bg-gray-50/50 p-3.5 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none cursor-pointer font-bold" required>
                                    <option value="sidebar">Sidebar</option>
                                    <option value="top_right">Header</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2 text-left">
                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest">Visibility</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($roles as $role)
                                    <label class="flex items-center p-2.5 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer hover:bg-white hover:border-blue-400 transition-all group">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                            x-model="menu.roles"
                                            class="w-4 h-4 rounded-md text-blue-600 border-gray-300 focus:ring-blue-500 transition-all">
                                        <span class="ml-2 text-[10px] font-black text-gray-600 uppercase tracking-tighter capitalize group-hover:text-blue-600 transition-colors">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between bg-blue-50/50 p-4 rounded-2xl border border-blue-100/50">
                            <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest">Active Status</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="menu.is_active" class="sr-only peer">
                                <div class="w-10 h-5.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full py-4 rounded-2xl bg-blue-600 text-xs font-black text-white shadow-xl shadow-blue-500/40 hover:bg-blue-700 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span x-text="editMode ? 'UPDATE ITEM' : 'SAVE CHANGES'"></span>
                    </button>
                    
                    <button type="button" @click="resetForm()" class="w-full py-2 text-[10px] font-black text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-[0.2em] mb-4">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
