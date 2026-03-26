@extends('layouts.app')

@section('header', 'Dashboard')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    
    $docCount = $isAdmin 
        ? \App\Models\Document::count() 
        : \App\Models\Document::where('uploaded_by', $user->id)->count();

    $catCount = \App\Models\Category::count();

    $sharedCount = \App\Models\FileUserShare::where('shared_to', $user->id)->count();

    $recentDocs = $isAdmin 
        ? \App\Models\Document::with('category')->latest()->limit(5)->get()
        : \App\Models\Document::with('category')->where('uploaded_by', $user->id)->latest()->limit(5)->get();
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full text-blue-600 mr-4">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ $isAdmin ? 'Total Documents' : 'My Documents' }}</p>
                <h3 class="text-2xl font-bold">{{ $docCount }}</h3>
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
                <h3 class="text-xl font-bold capitalize">{{ $user->role->name }}</h3>
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
    <h3 class="text-lg font-bold mb-4">Recent Documents</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Title</th>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Category</th>
                    <th class="text-left text-xs font-bold text-gray-500 uppercase pb-3">Uploaded</th>
                    <th class="text-right text-xs font-bold text-gray-500 uppercase pb-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($recentDocs as $doc)
                    <tr>
                        <td class="py-3 text-sm font-medium">{{ $doc->title }}</td>
                        <td class="py-3 text-sm text-gray-500">
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $doc->category->name }}</span>
                        </td>
                        <td class="py-3 text-sm text-gray-400">{{ $doc->created_at->diffForHumans() }}</td>
                        <td class="py-3 text-right">
                            <a href="{{ route('documents.preview', $doc->id) }}" class="text-blue-500 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
