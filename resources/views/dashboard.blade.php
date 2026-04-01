@extends('layouts.app')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full text-blue-600 mr-4">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ $isAdmin ? 'Total Files' : 'My Files' }}</p>
                <h3 class="text-2xl font-bold">{{ $fileCount }}</h3>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full text-green-600 mr-4">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Categories</p>
                <h3 class="text-2xl font-bold">{{ $catCount }}</h3>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full text-purple-600 mr-4">
                <i data-lucide="user" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Current Role</p>
                <h3 class="text-xl font-bold capitalize">{{ auth()->user()->role->name }}</h3>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-amber-500">
        <div class="flex items-center">
            <div class="p-3 bg-amber-100 rounded-full text-amber-600 mr-4">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Shared With Me</p>
                <h3 class="text-2xl font-bold">{{ $sharedCount }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-bold mb-4">Recent Files</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Name</th>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Type</th>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Size</th>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Uploaded</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($recentFiles as $file)
                    <tr>
                        <td class="py-3 text-sm font-medium">{{ $file->display_name }}</td>
                        <td class="py-3 text-sm text-gray-500">
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ strtoupper($file->extension) }}</span>
                        </td>
                        <td class="py-3 text-sm text-gray-500">{{ $file->size }} B</td>
                        <td class="py-3 text-sm text-gray-400">{{ $file->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
