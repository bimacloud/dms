@extends('layouts.app')

@section('header', 'Shared with Me')

@section('content')
<div x-data="{ 
    previewUrl: null, 
    previewType: null,
    fileName: ''
}">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Shared Documents</h1>
            <p class="text-xs text-gray-500 mt-0.5">Files your colleagues have shared with you.</p>
        </div>
    </div>

    <!-- Document Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse ($shares as $share)
            <div class="bg-white rounded-xl shadow-sm border border-gray-50 overflow-hidden group hover:border-blue-200 transition-all">
                <div class="aspect-square bg-gray-50 flex items-center justify-center relative group-hover:bg-blue-50 transition-colors cursor-pointer"
                    @click="previewUrl = '{{ route('documents.preview', $share->shareable->id) }}'; previewType = '{{ $share->shareable->mime_type }}'; fileName = '{{ addslashes($share->shareable->display_name) }}'">
                    @if(str_contains($share->shareable->mime_type, 'image'))
                        <img src="{{ route('documents.preview', $share->shareable->id) }}" class="w-full h-full object-cover">
                    @else
                        <i data-lucide="file-text" class="w-12 h-12 text-gray-200 group-hover:text-red-300 transition-colors"></i>
                    @endif
                    
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button @click.stop="previewUrl = '{{ route('documents.preview', $share->shareable->id) }}'; previewType = '{{ $share->shareable->mime_type }}'; fileName = '{{ addslashes($share->shareable->display_name) }}'" 
                            class="p-2 bg-white rounded-lg hover:text-blue-600 transition-colors" title="View">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                        
                        @if($share->permission === 'edit' || $share->permission === 'view')
                            <a href="{{ route('documents.download', $share->shareable->id) }}" class="p-2 bg-white rounded-lg hover:text-green-600 transition-colors" title="Download">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="p-3">
                    <div class="flex justify-between items-start mb-1">
                        @if($share->shareable->category)
                            <span class="text-[8px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">{{ $share->shareable->category->name }}</span>
                        @endif
                        @if($share->permission === 'edit')
                            <span class="text-[8px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded uppercase">Full Access</span>
                        @else
                            <span class="text-[8px] font-bold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded uppercase">View Only</span>
                        @endif
                    </div>
                    <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $share->shareable->display_name }}">{{ $share->shareable->display_name }}</h4>
                    <p class="text-[9px] text-gray-400 mt-0.5">From: {{ $share->owner->name }}</p>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">No Shared Documents</p>
                <p class="text-xs text-gray-400 mt-1">When someone shares a file with you, it will appear here.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $shares->links() }}
    </div>

    <!-- Unified Preview Modal -->
    <template x-if="previewUrl">
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" @keydown.escape.window="previewUrl = null">
            <div class="absolute inset-0" @click="previewUrl = null"></div>
            
            <div class="relative w-full max-w-5xl h-[85vh] bg-gray-900 rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-800">
                <div class="flex items-center justify-between px-4 py-3 bg-black/50 border-b border-gray-800 absolute top-0 inset-x-0 z-10 transition-opacity hover:opacity-100 opacity-90">
                    <h3 class="text-sm font-bold text-white truncate pr-4" x-text="fileName"></h3>
                    <div class="flex items-center gap-2">
                        <a :href="previewUrl" target="_blank" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10" title="Open in new tab">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                        <button @click="previewUrl = null" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10 bg-white/5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 bg-gray-900/50 flex items-center justify-center p-0 mt-12 overflow-hidden">
                    <template x-if="previewType && previewType.includes('image')">
                        <img :src="previewUrl" class="max-w-full max-h-full object-contain rounded-lg">
                    </template>
                    <template x-if="previewType === 'application/pdf'">
                        <iframe :src="previewUrl" class="w-full h-full rounded-lg bg-white" frameborder="0"></iframe>
                    </template>
                    <template x-if="!previewType || (!previewType.includes('image') && previewType !== 'application/pdf')">
                        <div class="text-center">
                            <i data-lucide="file-question" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i>
                            <p class="text-gray-400 font-medium">No preview available for this format</p>
                            <a :href="previewUrl" target="_blank" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Download File
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
