<?php
 
namespace App\Http\Controllers;
 
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
 
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
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:10240',
        ]);
 
        try {
            $setting = Setting::first();
            if (!$setting) {
                $setting = new Setting();
            }
            
            $setting->company_name = $request->company_name;
            $setting->company_subtitle = $request->company_subtitle;
 
            if ($request->hasFile('company_logo')) {
                // Delete old logo if exists
                if ($setting->company_logo) {
                    Storage::disk('public')->delete($setting->company_logo);
                }
                
                // Store new logo
                $path = $request->file('company_logo')->store('settings', 'public');
                $setting->company_logo = $path;
            }
 
            $setting->save();
 
            return redirect()->back()->with('success', 'Company settings updated successfully.');
        } catch (Exception $e) {
            Log::error('Failed to update company settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }
}
