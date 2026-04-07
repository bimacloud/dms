<!-- Share Modal -->
<div x-show="showShareModal" 
     class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
     x-transition.opacity 
     x-cloak>
    
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col"
         @click.away="showShareModal = false"
         x-data="{ 
            activeTab: 'public',
            isGenerating: false,
            generatedLink: '{{ session('share_link') ?? '' }}',
            error: '',
            init() {
                if (this.generatedLink) {
                    this.activeTab = 'public';
                }
            },
            async generatePublicLink() {
                this.isGenerating = true;
                this.error = '';
                
                try {
                    const formData = new FormData(this.$refs.publicForm);
                    const response = await fetch('{{ route('share_links.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.generatedLink = data.link;
                    } else {
                        this.error = data.message || 'Something went wrong';
                    }
                } catch (err) {
                    this.error = 'Failed to generate link';
                } finally {
                    this.isGenerating = false;
                }
            }
         }"
         @open-share-modal.window="generatedLink = ''; error = '';">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-gray-800" x-text="shareModalDocType === 'file' ? 'Share Document' : 'Share Folder'"></h3>
                <p class="text-xs text-gray-500 mt-1" x-text="shareModalDocTitle"></p>
            </div>
            <button @click="showShareModal = false" class="text-gray-400 hover:text-gray-600 transition-colors p-2 bg-white rounded-full shadow-sm hover:shadow">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        @if($errors->any())
            <div class="px-6 py-3 bg-red-50 border-b border-red-100">
                <p class="text-xs font-bold text-red-600">{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- Success View (Pop-up style) -->
        <div x-show="generatedLink" x-transition class="p-8 text-center">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-100 shadow-sm">
                <i data-lucide="check-circle-2" class="w-8 h-8 text-green-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Share Link Ready!</h3>
            <p class="text-xs text-gray-500 mb-6">Anyone with this link can now access the document.</p>
            
            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 mb-6 group">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-left mb-2 ml-1">Generated URL</p>
                <div class="flex items-center gap-2">
                    <input type="text" readonly :value="generatedLink" class="flex-1 text-xs px-3 py-2.5 bg-white border border-gray-200 rounded-xl outline-none text-gray-600 font-medium" id="shareLinkInputAjx">
                    <button type="button" @click="navigator.clipboard.writeText(generatedLink); alert('Link copied to clipboard!')" 
                            class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-2">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        Copy
                    </button>
                </div>
            </div>

            <button @click="showShareModal = false; generatedLink = ''" class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl shadow-lg transition-all">
                Done
            </button>
        </div>

        <!-- Tabs & Forms (Hidden on success) -->
        <div x-show="!generatedLink">
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
                <form x-ref="publicForm" @submit.prevent="generatePublicLink()" class="space-y-4">
                    @csrf
                    <input type="hidden" name="shareable_id" :value="shareModalDocId">
                    <input type="hidden" name="shareable_type" :value="shareModalDocType">
                    
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
                                <input type="datetime-local" name="expires_at" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" :required="enableExpiry">
                            </div>
                        </div>

                        <div class="mt-6 text-right">
                            <button type="submit" 
                                    :disabled="isGenerating"
                                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 ml-auto">
                                <span x-show="!isGenerating">Generate Public Link</span>
                                <span x-show="isGenerating">Generating...</span>
                                <i x-show="isGenerating" data-lucide="loader" class="w-3 h-3 animate-spin"></i>
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
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Colleague Email</label>
                        <input type="text" name="email" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Enter user's email or 'all'" required>
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
