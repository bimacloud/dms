<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StorageProvider;

class StorageProviderController extends Controller
{
    public function index()
    {
        $providers = StorageProvider::all();
        return view('settings.storage', compact('providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|string|in:s3',
            'key' => 'required|string',
            'secret' => 'required|string',
            'region' => 'nullable|string',
            'bucket' => 'required|string',
            'endpoint' => 'required|url',
            'use_path_style_endpoint' => 'boolean',
        ]);

        $data = $request->all();
        $data['use_path_style_endpoint'] = $request->has('use_path_style_endpoint');
        
        if ($request->has('is_default')) {
            StorageProvider::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        StorageProvider::create($data);

        return redirect()->back()->with('success', 'Storage provider added successfully.');
    }

    public function update(Request $request, StorageProvider $storageProvider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string',
            'secret' => 'nullable|string',
            'region' => 'nullable|string',
            'bucket' => 'required|string',
            'endpoint' => 'required|url',
        ]);

        $data = $request->all();
        $data['use_path_style_endpoint'] = $request->has('use_path_style_endpoint');
        $data['is_active'] = $request->has('is_active');
        
        if ($request->has('is_default')) {
            StorageProvider::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        if (!$request->secret) {
            unset($data['secret']);
        }

        $storageProvider->update($data);

        return redirect()->back()->with('success', 'Storage provider updated successfully.');
    }

    public function destroy(StorageProvider $storageProvider)
    {
        if ($storageProvider->is_default) {
            return redirect()->back()->with('error', 'Cannot delete the default storage provider.');
        }

        $storageProvider->delete();

        return redirect()->back()->with('success', 'Storage provider deleted successfully.');
    }
}
