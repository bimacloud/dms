@extends('layouts.app')

@section('header', 'Storage Settings')

@section('content')
<div x-data="{ 
    showAddModal: false, 
    editingProvider: { name: '', driver: 's3', endpoint: '', key: '', bucket: '', use_path_style_endpoint: true, is_default: false, is_active: true },
    isEdit: false,
    openModal(provider = null) {
        if (provider) {
            this.editingProvider = JSON.parse(JSON.stringify(provider));
            this.isEdit = true;
        } else {
            this.editingProvider = { name: '', driver: 's3', endpoint: '', key: '', bucket: '', use_path_style_endpoint: true, is_default: false, is_active: true };
            this.isEdit = false;
        }
        this.showAddModal = true;
    }
}">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Storage Providers</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage multiple S3/MinIO endpoints for your cloud drive.</p>
        </div>
        <button @click="openModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition-all gap-2 shadow-lg shadow-blue-200">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            ADD PROVIDER
        </button>
    </div>

    <!-- Providers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($providers as $provider)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:border-blue-200 transition-all group">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <i data-lucide="database" class="w-5 h-5"></i>
                        </div>
                        <div class="flex gap-2">
                            @if($provider->is_default)
                                <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[10px] font-bold rounded-lg uppercase">Default</span>
                            @endif
                            @if($provider->is_active)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-lg uppercase">Active</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-50 text-gray-400 text-[10px] font-bold rounded-lg uppercase">Inactive</span>
                            @endif
                        </div>
                    </div>
                    
                    <h3 class="font-bold text-gray-900">{{ $provider->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1 truncate">{{ $provider->endpoint }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-50 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Bucket</p>
                            <p class="text-xs font-medium text-gray-700">{{ $provider->bucket }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Driver</p>
                            <p class="text-xs font-medium text-gray-700 uppercase">{{ $provider->driver }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-2">
                    <button @click="openModal({{ $provider->toJson() }})" class="text-xs font-bold text-blue-600 hover:text-blue-700 p-2">
                        EDIT
                    </button>
                    @if(!$provider->is_default)
                        <form action="{{ route('settings.storage.destroy', $provider->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-bold text-red-600 hover:text-red-700 p-2">
                                DELETE
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showAddModal" 
         @click.self="showAddModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
         x-transition x-cloak>
        <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900" x-text="isEdit ? 'Edit Provider' : 'Add Storage Provider'"></h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form :action="isEdit ? `/settings/storage/${editingProvider.id}` : '{{ route('settings.storage.store') }}'" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Provider Name</label>
                        <input type="text" name="name" x-model="editingProvider.name" required 
                            class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all">
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Driver</label>
                        <select name="driver" x-model="editingProvider.driver" class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all">
                            <option value="s3">S3 / MinIO</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Region</label>
                        <input type="text" name="region" x-model="editingProvider.region" 
                            class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all">
                    </div>

                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Endpoint URL</label>
                        <input type="url" name="endpoint" x-model="editingProvider.endpoint" required 
                            placeholder="http://172.29.2.10:9000"
                            class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Access Key</label>
                        <input type="text" name="key" x-model="editingProvider.key" required 
                            class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Secret Key</label>
                        <input type="password" name="secret" :required="!isEdit"
                            class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all"
                            :placeholder="isEdit ? 'Leave blank to keep current' : ''">
                    </div>

                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Bucket Name</label>
                        <input type="text" name="bucket" x-model="editingProvider.bucket" required 
                            class="w-full rounded-xl border-gray-100 bg-gray-50 p-3 text-sm focus:bg-white focus:border-blue-400 border outline-none transition-all">
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4 border-t border-gray-50">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="use_path_style_endpoint" value="1" class="sr-only" x-model="editingProvider.use_path_style_endpoint">
                            <div class="w-10 h-5 bg-gray-200 rounded-full transition-colors group-hover:bg-gray-300" :class="editingProvider.use_path_style_endpoint ? 'bg-blue-600' : ''"></div>
                            <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-transform" :class="editingProvider.use_path_style_endpoint ? 'translate-x-5' : ''"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-600 uppercase">Use Path Style Endpoint (Required for MinIO)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="is_default" value="1" class="sr-only" x-model="editingProvider.is_default">
                            <div class="w-10 h-5 bg-gray-200 rounded-full transition-colors group-hover:bg-gray-300" :class="editingProvider.is_default ? 'bg-blue-600' : ''"></div>
                            <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-transform" :class="editingProvider.is_default ? 'translate-x-5' : ''"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-600 uppercase">Set as Default Provider</span>
                    </label>

                    <template x-if="isEdit">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" class="sr-only" x-model="editingProvider.is_active">
                                <div class="w-10 h-5 bg-gray-200 rounded-full transition-colors group-hover:bg-gray-300" :class="editingProvider.is_active ? 'bg-blue-600' : ''"></div>
                                <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-transform" :class="editingProvider.is_active ? 'translate-x-5' : ''"></div>
                            </div>
                            <span class="text-xs font-bold text-gray-600 uppercase">Status: Active</span>
                        </label>
                    </template>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="showAddModal = false" class="px-6 py-3 text-xs font-bold text-gray-500 hover:text-gray-700 transition-colors uppercase">
                        Cancel
                    </button>
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 uppercase">
                        Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
