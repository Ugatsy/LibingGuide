<nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
     x-data="{ mobileOpen: false, servicesOpen: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 60 })"
     :class="scrolled ? 'bg-white/95 backdrop-blur-sm shadow-sm' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/heritage-logo.png') }}" alt="Heritage Memorial Park" class="h-10 w-auto">
                <span class="text-xl font-bold tracking-tight text-gray-900">Heritage Memorial Park</span>
            </a>

            <div class="hidden lg:flex items-center gap-8">
                <a href="{{ route('home') }}#about" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">About</a>

                <div x-data="{ open: false }" @mouseleave="open = false" class="relative">
                    <button @mouseenter="open = true" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">
                        Our Services
                        <svg class="ms-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @mouseenter="open = true" @mouseleave="open = false" x-transition class="absolute left-0 mt-2 w-56 z-50" x-cloak>
                        <div class="bg-white rounded-xl shadow-lg ring-1 ring-black/5 py-2 overflow-hidden">
                            <a href="{{ route('public.lots') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M3 7l9-4 9 4M3 14h18v7H3z"/></svg>
                                Memorial Lots
                            </a>
                            <a href="{{ route('public.plans') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Pre-Need Plans
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <div class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-400 cursor-default">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <span>Columbarium <span class="text-xs text-amber-500 ml-1">Coming Soon</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('public.find') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Find a Loved One</a>
                <a href="{{ route('home') }}#contact" class="text-sm font-medium text-gray-600 hover:text-emerald-800 transition-colors">Contact</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors shadow-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border-2 border-emerald-700 text-emerald-700 font-semibold rounded-lg hover:bg-emerald-50 transition-colors">Staff Login</a>
                @endauth
            </div>

            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': mobileOpen, 'block': !mobileOpen }" class="block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'block': mobileOpen, 'hidden': !mobileOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="mobileOpen" class="lg:hidden border-t bg-white/95 backdrop-blur-sm" x-cloak>
        <div class="px-4 py-4 space-y-3">
            <a href="{{ route('home') }}#about" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">About</a>

            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-sm font-medium text-gray-600 hover:text-emerald-800">
                    Our Services
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                </button>
                <div x-show="open" class="mt-2 ml-4 space-y-2">
                    <a href="{{ route('public.lots') }}" @click="mobileOpen = false" class="block text-sm text-gray-500 hover:text-emerald-800">Memorial Lots</a>
                    <a href="{{ route('public.plans') }}" @click="mobileOpen = false" class="block text-sm text-gray-500 hover:text-emerald-800">Pre-Need Plans</a>
                    <span class="block text-sm text-gray-400 cursor-default">Columbarium <span class="text-xs text-amber-500">Coming Soon</span></span>
                </div>
            </div>

            <a href="{{ route('public.find') }}" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Find a Loved One</a>
            <a href="{{ route('home') }}#contact" @click="mobileOpen = false" class="block text-sm font-medium text-gray-600 hover:text-emerald-800">Contact</a>
            <hr class="border-gray-200">
            @auth
                <a href="{{ route('dashboard') }}" @click="mobileOpen = false" class="block text-center px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-lg">Dashboard</a>
            @else
                <a href="{{ route('login') }}" @click="mobileOpen = false" class="block text-center px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-lg">Staff Login</a>
            @endauth
        </div>
    </div>
</nav>