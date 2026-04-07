<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found | DMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center p-6 selection:bg-blue-100 selection:text-blue-600">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-400/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-md w-full text-center">
        <!-- Icon Container -->
        <div class="relative inline-block mb-8">
            <div class="w-32 h-32 bg-white rounded-[40px] shadow-2xl shadow-blue-200/50 flex items-center justify-center rotate-3 hover:rotate-0 transition-transform duration-500 group">
                <div class="w-24 h-24 bg-blue-50 rounded-[32px] flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <i data-lucide="search-x" class="w-12 h-12 text-blue-500 animate-bounce"></i>
                </div>
            </div>
            <!-- Decorative dots -->
            <div class="absolute -top-4 -right-4 w-4 h-4 bg-blue-500 rounded-full animate-ping"></div>
            <div class="absolute -bottom-2 -left-4 w-2 h-2 bg-indigo-400 rounded-full"></div>
        </div>

        <!-- Text Content -->
        <div class="space-y-4 mb-10">
            <h1 class="text-6xl font-black text-gray-900 tracking-tighter">404</h1>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Page Not Found</h2>
            <p class="text-sm text-gray-500 leading-relaxed max-w-[280px] mx-auto font-medium">
                Oops! The page you're looking for seems to have vanished into the digital void.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white rounded-2xl font-bold text-xs shadow-xl shadow-gray-200 hover:bg-black hover:-translate-y-1 transition-all">
                <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                Return Home
            </a>
            <button onclick="window.history.back()" class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-700 border border-gray-100 rounded-2xl font-bold text-xs shadow-sm hover:bg-gray-50 hover:shadow-md transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Go Back
            </button>
        </div>

        <!-- Footer -->
        <div class="mt-16 pt-8 border-t border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Document Management System</p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
