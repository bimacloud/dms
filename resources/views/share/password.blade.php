<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Document Share</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="bg-blue-600 px-6 py-8 text-center text-white">
            <div class="w-16 h-16 bg-white/20 rounded-2xl mx-auto flex items-center justify-center mb-4 backdrop-blur-sm shadow-sm">
                <i data-lucide="lock" class="w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight">Protected Document</h2>
            <p class="text-xs text-blue-100 mt-1">This shared link is password protected</p>
        </div>

        <div class="p-8">
            <form action="{{ route('share.password.verify', $share->token) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Enter Password</label>
                    <div class="relative">
                        <i data-lucide="key" class="absolute left-3 top-3 w-5 h-5 text-gray-400"></i>
                        <input type="password" name="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm" placeholder="••••••••" required autofocus>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-500 font-bold mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <span>Access Document</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
