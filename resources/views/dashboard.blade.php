@extends('layouts.app')

@section('header', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Welcome Header & Storage Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Personalized Greeting -->
        <div class="lg:col-span-2 relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-blue-500/20 group">
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/10 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>
            <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-white/5 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                        Selamat datang kembali, <br class="md:hidden"> {{ explode(' ', auth()->user()->name)[0] }}!
                    </h1>
                    <p class="text-blue-100/70 text-sm max-w-md leading-relaxed">
                        Kelola dokumen Anda dengan lancar. Ruang kerja Anda telah dioptimalkan dan siap untuk tugas hari ini.
                    </p>
                </div>
                
                <div class="mt-8 flex gap-3">
                    <a href="{{ route('drive.index') }}" class="px-6 py-3 bg-white text-blue-600 font-bold text-xs rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all duration-300">
                        Buka Drive Saya
                    </a>
                    <button onclick="document.getElementById('hiddenFileInputMain').click()" class="px-6 py-3 bg-blue-500/30 backdrop-blur-md text-white border border-white/20 font-bold text-xs rounded-2xl hover:bg-white/20 transition-all duration-300">
                        Unggah Cepat
                    </button>
                    <input type="file" id="hiddenFileInputMain" class="hidden" onchange="window.location='{{ route('drive.index') }}'">
                </div>
            </div>
        </div>

        <!-- Storage Usage Card -->
        <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-gray-200/50 border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-sm font-bold text-gray-900">Penggunaan Penyimpanan</h3>
                    <div class="p-2 bg-gray-50 rounded-xl">
                        <i data-lucide="database" class="w-4 h-4 text-gray-400"></i>
                    </div>
                </div>

                <div class="relative pt-2">
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-3xl font-black text-gray-900">{{ $storageUsedFormatted }}</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pb-1">dari {{ $storageQuotaFormatted }}</span>
                    </div>
                    
                    <div class="w-full bg-gray-100 rounded-full h-3 mb-2 overflow-hidden overflow-hidden p-[2px]">
                        @php
                            $barColor = 'bg-blue-600';
                            if ($storagePercentage > 70) $barColor = 'bg-amber-500';
                            if ($storagePercentage > 90) $barColor = 'bg-red-500';
                        @endphp
                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $storagePercentage }}%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-500">{{ $storagePercentage }}% Terpakai</span>
                        <span class="text-[10px] font-bold text-blue-600 hover:underline cursor-pointer">Upgrade Plan</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50 space-y-3">
                <div class="flex items-center justify-between text-xs font-medium">
                    <span class="text-gray-500">Peran Anda</span>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full font-bold capitalize">{{ auth()->user()->role->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats & Actions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ !$isAdmin ? '2' : '3' }} gap-6">
        <!-- My Files -->
        <a href="{{ route('drive.index') }}" class="group bg-white p-6 rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 transition-all duration-300 hover:shadow-xl hover:border-blue-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Dokumen Saya</h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $fileCount }} File • {{ $folderCount }} Folder</p>
                </div>
                <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-blue-600"></i>
                </div>
            </div>
        </a>

        <!-- Shared Items -->
        <a href="{{ route('shared.index') }}" class="group bg-white p-6 rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 transition-all duration-300 hover:shadow-xl hover:border-indigo-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Dibagikan</h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $sharedCount }} File Dibagikan</p>
                </div>
                <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-indigo-600"></i>
                </div>
            </div>
        </a>

        @if($isAdmin)
        <!-- Categories (Hidden for Members) -->
        <a href="{{ route('categories.index') }}" class="group bg-white p-6 rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 transition-all duration-300 hover:shadow-xl hover:border-amber-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Kategori</h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $catCount }} Aktif</p>
                </div>
                <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-amber-600"></i>
                </div>
            </div>
        </a>
        @endif
    </div>

    @if($recentFolders->isNotEmpty())
    <!-- Recent Folders Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 flex items-center gap-2">
                <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                Folder Terbaru
            </h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($recentFolders as $folder)
                <a href="{{ route('drive.index', $folder->id) }}" class="group bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all hover:border-amber-200">
                    <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center mb-3 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300">
                        <i data-lucide="folder" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-[11px] font-black text-gray-900 truncate group-hover:text-amber-600">{{ $folder->name }}</h4>
                    <p class="text-[9px] text-gray-400 font-bold mt-0.5 uppercase tracking-tighter">{{ $folder->files_count ?? '0' }} Dokumen</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Files Section -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
            <h3 class="text-sm font-black text-gray-900 flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-600 rounded-full"></span>
                Dokumen Terbaru
            </h3>
            <a href="{{ route('drive.index') }}" class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline flex items-center gap-1">
                Lihat Semua Drive <i data-lucide="move-right" class="w-3 h-3"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                        <th class="px-6 py-4 text-left">Nama Dokumen</th>
                        <th class="px-6 py-4 text-left">Format</th>
                        <th class="px-6 py-4 text-left">Ukuran</th>
                        <th class="px-6 py-4 text-left">Tanggal Unggah</th>
                        <th class="px-6 py-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($recentFiles as $file)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center mr-4 group-hover:bg-white group-hover:shadow-sm border border-transparent group-hover:border-blue-100 transition-all">
                                        @php
                                            $icon = 'file-text';
                                            if (str_contains($file->mime_type, 'pdf')) $icon = 'file-type-2';
                                            if (str_contains($file->mime_type, 'image')) $icon = 'image';
                                            if (str_contains($file->mime_type, 'zip')) $icon = 'archive';
                                        @endphp
                                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-gray-900 group-hover:text-blue-600 transition-colors">{{ $file->display_name }}</h4>
                                        <p class="text-[9px] font-bold text-gray-400 mt-0.5 uppercase tracking-tighter">{{ $file->mime_type }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-white text-[9px] font-black text-gray-500 border border-gray-100 rounded-lg group-hover:border-blue-200 transition-colors shadow-sm">
                                    {{ strtoupper($file->extension) ?: 'BIN' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[10px] font-black text-gray-600">
                                {{ $file->size }} B
                            </td>
                            <td class="px-6 py-4 text-[10px] font-medium text-gray-400">
                                {{ $file->created_at->translatedFormat('d M Y') }}
                                <span class="block text-[8px] opacity-70">{{ $file->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('documents.download', $file->id) }}" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:text-green-500 hover:bg-green-50 flex items-center justify-center transition-all" title="Download">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
