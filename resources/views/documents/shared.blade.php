@extends('layouts.app')

@section('header', 'Shared with Me')

@section('content')
<div>
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
                <div class="aspect-square bg-gray-50 flex items-center justify-center relative group-hover:bg-blue-50 transition-colors">
                    @if(str_contains($share->document->file_type, 'image'))
                        <img src="{{ route('documents.preview', $share->document->id) }}" class="w-full h-full object-cover">
                    @else
                        <i data-lucide="file-text" class="w-12 h-12 text-gray-200 group-hover:text-red-300 transition-colors"></i>
                    @endif
                    
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <a href="{{ route('documents.preview', $share->document->id) }}" target="_blank" class="p-2 bg-white rounded-lg hover:text-blue-600 transition-colors" title="View in New Tab">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                        
                        @if($share->permission === 'download' || $share->permission === 'view')
                            <!-- Temp download via auth bypass using token generation endpoint -->
                            <form action="{{ route('download.generate.auth') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="document_id" value="{{ $share->document->id }}">
                                <button class="p-2 bg-white rounded-lg hover:text-green-600 transition-colors" title="Download">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="p-3">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[8px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">{{ $share->document->category->name ?? 'Uncategorized' }}</span>
                        @if($share->permission === 'download')
                            <span class="text-[8px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded uppercase">Full Access</span>
                        @else
                            <span class="text-[8px] font-bold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded uppercase">View Only</span>
                        @endif
                    </div>
                    <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $share->document->title }}">{{ $share->document->title }}</h4>
                    <p class="text-[9px] text-gray-400 mt-0.5">From: {{ $share->sharedBy->name }}</p>
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
</div>
@endsection
