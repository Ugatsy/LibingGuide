<nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/heritage-logo.png') }}" alt="Heritage Memorial Park" class="h-10 w-auto">
                <span class="text-xl font-bold text-gray-900 tracking-tight">Heritage Memorial Park</span>
            </a>

            <div class="hidden lg:flex items-center gap-8">
                <a href="{{ route('home') }}#about" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">About Us</a>
                <a href="{{ route('public.lots') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Memorial Lots</a>
                <a href="{{ route('public.plans') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Pre-Need Plans</a>
                <a href="{{ route('public.find') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Find a Loved One</a>
                <a href="{{ route('home') }}#careers" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Careers</a>
                <a href="{{ route('home') }}#contact" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Log in</a>
                @endauth
            </div>

            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': mobileOpen, 'block': !mobileOpen }" class="block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" class="lg:hidden border-t bg-white" x-cloak>
        <div class="px-4 py-4 space-y-3">
            <a href="{{ route('home') }}#about" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">About Us</a>
            <a href="{{ route('public.lots') }}" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Memorial Lots</a>
            <a href="{{ route('public.plans') }}" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Pre-Need Plans</a>
            <a href="{{ route('public.find') }}" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Find a Loved One</a>
            <a href="{{ route('home') }}#careers" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Careers</a>
            <a href="{{ route('home') }}#contact" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Contact</a>
            <hr class="border-gray-200">
            @auth
                <a href="{{ route('dashboard') }}" @click="mobileOpen = false" class="block text-center px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-lg">Dashboard</a>
            @else
                <a href="{{ route('login') }}" @click="mobileOpen = false" class="block text-center px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-lg">Log in</a>
            @endauth
        </div>
    </div>
</nav>
