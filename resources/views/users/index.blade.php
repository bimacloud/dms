@extends('layouts.app')

@section('header', 'User Management')

@section('content')
<div x-data="{ 
    showForm: false, 
    editMode: false, 
    user: { id: '', name: '', email: '', role_id: '', position_id: '', disk_quota_value: '', disk_quota_unit: 'GB' },
    search: {{ Js::from(request('search')) }},
    submitSearch() {
        const url = new URL(window.location.href);
        url.searchParams.set('search', this.search);
        window.location.href = url.toString();
    },
    resetForm() {
        this.user = { id: '', name: '', email: '', role_id: '', position_id: '', disk_quota_value: '', disk_quota_unit: 'GB' };
        this.editMode = false;
        this.showForm = false;
    },
    parseQuota(bytes) {
        if (!bytes) return { val: '', unit: 'GB' };
        if (bytes >= 1099511627776 && bytes % 1099511627776 === 0) return { val: bytes / 1099511627776, unit: 'TB' };
        if (bytes >= 1073741824 && bytes % 1073741824 === 0) return { val: bytes / 1073741824, unit: 'GB' };
        if (bytes >= 1048576 && bytes % 1048576 === 0) return { val: bytes / 1048576, unit: 'MB' };
        return { val: Math.round(bytes / 1073741824), unit: 'GB' };
    },
    editUser(u) {
        let q = this.parseQuota(u.raw_quota);
        this.user = { 
            id: u.id, 
            name: u.name, 
            email: u.email, 
            role_id: u.role_id, 
            position_id: u.position_id,
            disk_quota_value: q.val,
            disk_quota_unit: q.unit
        };
        this.editMode = true;
        this.showForm = true;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}">
    <!-- Premium Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">User Management</h1>
            <p class="text-sm text-gray-500 mt-1 uppercase tracking-tighter font-bold">Control access and identities</p>
        </div>
        <button @click="if(showForm && !editMode) { showForm = false } else { resetForm(); showForm = true }"
            class="flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-xl transition-all active:scale-95"
            :class="showForm && !editMode ? 'bg-gray-800 hover:bg-gray-700' : 'bg-blue-600 hover:bg-blue-500 shadow-blue-200'">
            <i :data-lucide="showForm && !editMode ? 'x' : 'user-plus'" class="w-4 h-4 mr-2"></i>
            <span x-text="showForm && !editMode ? 'Cancel' : 'Add New User'"></span>
        </button>
    </div>

    <!-- Inline User Form (Alur Non-Popup) -->
    <div x-show="showForm" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-10" x-cloak>
        
        <div class="bg-white rounded-[2rem] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="bg-gray-50/50 px-8 py-4 border-b border-gray-100 flex items-center justify-between">
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em]" x-text="editMode ? 'Update Account Information' : 'Register New Team Member'"></span>
                <button @click="resetForm()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('users') }}/' + user.id : '{{ route('users.store') }}'" method="POST" class="p-8">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Full Name</label>
                        <input type="text" name="name" x-model="user.name" 
                            class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium" 
                            required placeholder="e.g. John Doe">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" x-model="user.email" 
                            class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium" 
                            required placeholder="john@example.com">
                    </div>

                    @if(auth()->user()->role->name === 'root')
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">User Role</label>
                        <select name="role_id" x-model="user.role_id" 
                            class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none cursor-pointer font-medium"> <!-- no longer required arbitrarily -->
                            <option value="">Select a role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Disk Quota</label>
                        <div class="flex gap-2">
                            <input type="number" name="disk_quota_value" x-model="user.disk_quota_value" step="any" min="0" placeholder="Unlimited"
                                class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium">
                            <select name="disk_quota_unit" x-model="user.disk_quota_unit" 
                                class="w-32 rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none cursor-pointer font-bold text-gray-700">
                                <option value="MB">MB</option>
                                <option value="GB">GB</option>
                                <option value="TB">TB</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Job Position</label>
                        <select name="position_id" x-model="user.position_id" 
                            class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none cursor-pointer font-medium">
                            <option value="">No Position</option>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos->id }}">{{ ucfirst($pos->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3 bg-blue-50/50 p-6 rounded-3xl border border-blue-100/50 border-dashed flex flex-col md:flex-row items-center gap-6">
                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-black text-blue-600 uppercase tracking-widest mb-2">
                                Password
                                <span x-show="editMode" class="ml-1 text-blue-400 lowercase font-bold italic">(Blank to keep current)</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" 
                                    class="w-full rounded-2xl border-blue-200/50 bg-white p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all border outline-none font-medium" 
                                    :required="!editMode" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="flex items-end h-full">
                            <button type="submit" 
                                class="px-12 py-4 rounded-2xl bg-blue-600 text-sm font-black text-white shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95 flex items-center gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                <span x-text="editMode ? 'UPDATE ACCOUNT' : 'CREATE USER'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- User DataTable -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" x-model="search" @keyup.enter="submitSearch()"
                    class="block w-full pl-11 pr-4 py-3 border border-gray-100 rounded-2xl bg-gray-50/50 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" 
                    placeholder="Search name or email...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Identity</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Role</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Position</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Storage Used</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($users as $u)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 border-2 border-white shadow-sm flex items-center justify-center text-blue-700 font-black text-sm uppercase">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">{{ $u->name }}</span>
                                        <span class="text-[11px] text-gray-500 font-medium">{{ $u->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $u->role->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($u->position)
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-purple-50 text-purple-600 border border-purple-100">
                                    {{ $u->position->name }}
                                </span>
                                @else
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[11px] font-black tracking-widest bg-blue-50 text-blue-700 border border-blue-100 uppercase">
                                    <i data-lucide="hard-drive" class="w-3 h-3 mr-2 text-blue-500"></i>
                                    {{ $u->total_disk_space }} / {{ $u->formatted_disk_quota }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <button @click="editUser({ id: '{{ $u->id }}', name: '{{ addslashes($u->name) }}', email: '{{ $u->email }}', role_id: '{{ $u->role_id }}', position_id: '{{ $u->position_id }}', raw_quota: {{ $u->disk_quota ? $u->disk_quota : 'null' }} })" 
                                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Edit User">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                @if(auth()->user()->role->name === 'root' || (auth()->user()->role->name === 'admin' && $u->role->name !== 'root'))
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this user?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Delete User">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <i data-lucide="user-x" class="w-12 h-12 text-gray-100 mx-auto mb-4"></i>
                                <p class="text-gray-400 text-sm font-medium">No users found in orbit.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
