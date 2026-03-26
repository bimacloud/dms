<!-- Share Modal -->
<div x-show="showShareModal" 
     class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
     x-transition.opacity 
     x-cloak>
    
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col"
         @click.away="showShareModal = false"
         x-data="{ activeTab: 'public' }">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Share Document</h3>
                <p class="text-xs text-gray-500 mt-1" x-text="shareModalDocTitle"></p>
            </div>
            <button @click="showShareModal = false" class="text-gray-400 hover:text-gray-600 transition-colors p-2 bg-white rounded-full shadow-sm hover:shadow">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Success Message from session -->
        @if(session('success') && session('share_link'))
            <div class="px-6 py-4 bg-green-50 border-b border-green-100 flex flex-col items-start gap-2">
                <p class="text-sm font-bold text-green-700">Link Generated:</p>
                <div class="flex items-center w-full gap-2">
                    <input type="text" readonly value="{{ session('share_link') }}" class="flex-1 text-xs px-3 py-2 bg-white border border-green-200 rounded-lg outline-none text-gray-600" id="shareLinkInput">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('shareLinkInput').value); alert('Copied!')" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg transition-colors">Copy</button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="px-6 py-3 bg-red-50 border-b border-red-100">
                <p class="text-xs font-bold text-red-600">{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- Tabs -->
        <div class="flex border-b border-gray-100 px-6 pt-2 bg-white">
            <button @click="activeTab = 'public'" 
                    class="px-4 py-3 text-xs font-bold transition-colors border-b-2"
                    :class="activeTab === 'public' ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-transparent hover:text-gray-600'">
                Public Link
            </button>
            <button @click="activeTab = 'internal'" 
                    class="px-4 py-3 text-xs font-bold transition-colors border-b-2"
                    :class="activeTab === 'internal' ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-transparent hover:text-gray-600'">
                Internal Users
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Public Link Form -->
            <div x-show="activeTab === 'public'" x-transition>
                <form action="{{ route('share_links.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="document_id" :value="shareModalDocId">
                    
                    <div x-data="{ enablePassword: false, enableExpiry: false }">
                        <div class="space-y-3">
                            <!-- Password Toggle -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">Password Protection</p>
                                    <p class="text-[10px] text-gray-500">Require password to access</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="enablePassword" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div x-show="enablePassword" x-transition x-cloak class="px-1">
                                <input type="password" name="password" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Enter secure password" :required="enablePassword">
                            </div>

                            <!-- Expiry Toggle -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">Link Expiration</p>
                                    <p class="text-[10px] text-gray-500">Automatically revoke access</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="enableExpiry" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div x-show="enableExpiry" x-transition x-cloak class="px-1">
                                <input type="datetime-local" name="expired_at" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" :required="enableExpiry">
                            </div>
                        </div>

                        <div class="mt-6 text-right">
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                Generate Public Link
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Internal User Form -->
            <div x-show="activeTab === 'internal'" x-transition x-cloak>
                <form action="{{ route('shared.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="document_id" :value="shareModalDocId">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Select Colleague</label>
                        <select name="user_id" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                            <option value="">Choose a user...</option>
                            <option value="all" class="font-bold text-blue-700 bg-blue-50">👥 Semua User (All Members & Admin)</option>
                            @if(isset($users))
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Permission Level</label>
                        <select name="permission" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                            <option value="view">View Only</option>
                            <option value="download">View & Download</option>
                        </select>
                    </div>

                    <div class="mt-6 text-right pt-2 border-t border-gray-50">
                        <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                            Share Internally
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
