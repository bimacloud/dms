<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate([]);
        return view('settings.company', compact('setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_subtitle' => 'nullable|string',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $setting = Setting::firstOrCreate([]);
        
        $data = $request->only(['company_name', 'company_subtitle']);

        if ($request->hasFile('company_logo')) {
            if ($setting->company_logo) {
                Storage::disk('public')->delete($setting->company_logo);
            }
            $path = $request->file('company_logo')->store('settings', 'public');
            $data['company_logo'] = $path;
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }
}
