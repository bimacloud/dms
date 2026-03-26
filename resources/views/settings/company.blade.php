@extends('layouts.app')

@section('header', 'Company Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Platform Branding</h2>
                <p class="text-sm text-gray-500 mt-1">Configure how your platform appears externally</p>
            </div>
            <i data-lucide="settings" class="w-8 h-8 text-blue-500"></i>
        </div>

        <form action="{{ route('settings.company.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf
            
            <div class="space-y-6">
                <!-- Logo Upload -->
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Company Logo (PNG/JPG)</label>
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                            @if($setting->company_logo)
                                <img src="{{ Storage::url($setting->company_logo) }}" class="w-full h-full object-contain p-2">
                            @else
                                <i data-lucide="image" class="w-8 h-8 text-gray-300"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="company_logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                            <p class="text-xs text-gray-400 mt-2">Recommended: Standard landscape or square image (Max 2MB)</p>
                        </div>
                    </div>
                </div>

                <div class="h-px w-full bg-gray-50 my-2"></div>

                <!-- Company Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $setting->company_name) }}" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium" required>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Company Subtitle / Tagline</label>
                        <textarea name="company_subtitle" rows="3" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm focus:ring-4 focus:ring-blue-500/10 focus:bg-white focus:border-blue-500 transition-all border outline-none font-medium">{{ old('company_subtitle', $setting->company_subtitle) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50 flex justify-end">
                <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-600 text-sm font-bold text-white shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
