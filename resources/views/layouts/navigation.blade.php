<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php $role = auth()->user()->role?->name; @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
                    @if(in_array($role, ['super_admin', 'rcc_staff']))
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">{{ __('Clients') }}</x-nav-link>
                    <x-nav-link :href="route('plots.index')" :active="request()->routeIs('plots.*') || request()->routeIs('burials.*') || request()->routeIs('deceased.*')">{{ __('Lots & Burials') }}</x-nav-link>
                    <x-nav-link :href="route('burial-permits.index')" :active="request()->routeIs('burial-permits.*')">{{ __('Permits (AF 58)') }}</x-nav-link>
                    @endif
                    <x-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*') || request()->routeIs('payments.*')">{{ __('Contracts & Billing') }}</x-nav-link>
                    @if(in_array($role, ['super_admin', 'rcc_staff']))
                    <div x-data="{ open: false }" @mouseleave="open = false" class="relative">
                        <button @mouseenter="open = true" class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out h-16 border-b-2 border-transparent">
                            {{ __('Services') }}
                            <svg class="ms-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @mouseenter="open = true" @mouseleave="open = false" x-transition class="absolute left-0 mt-0 pt-1 w-56 z-[1000]" x-cloak>
                            <div class="bg-white rounded-lg shadow-lg ring-1 ring-black/5 py-2">
                                <a href="{{ route('pre-need-plans.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('pre-need-plans.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Pre-Need Plans</a>
                                <a href="{{ route('columbary-niches.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('columbary-niches.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Columbary Niches</a>
                                @if($role === 'super_admin')
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('burial-spots.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('burial-spots.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Legacy Map</a>
                                <a href="{{ route('cemetery.admin') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('cemetery.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Cemetery Map</a>
                                <a href="{{ route('paths.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('paths.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Pathways</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <x-nav-link :href="route('inquiries.index')" :active="request()->routeIs('inquiries.*')">{{ __('Inquiries') }}</x-nav-link>
                    <x-nav-link :href="route('client-notifications.index')" :active="request()->routeIs('client-notifications.*')">{{ __('Client Notifs') }}</x-nav-link>
                    @endif
                    @if($role === 'engr')
                    <div x-data="{ open: false }" @mouseleave="open = false" class="relative">
                        <button @mouseenter="open = true" class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out h-16 border-b-2 border-transparent">
                            {{ __('Map') }}
                            <svg class="ms-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @mouseenter="open = true" @mouseleave="open = false" x-transition class="absolute left-0 mt-0 pt-1 w-48 z-[1000]" x-cloak>
                            <div class="bg-white rounded-lg shadow-lg ring-1 ring-black/5 py-2">
                                <a href="{{ route('cemetery.admin') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('cemetery.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Cemetery Polygons</a>
                                <a href="{{ route('plots.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('plots.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Burial Plotting & Blocks</a>
                                <a href="{{ route('paths.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->routeIs('paths.*') ? 'bg-emerald-50 text-emerald-800 font-medium' : '' }}">Map Pathing</a>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($role === 'super_admin')
                    <x-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">{{ __('Activity Logs') }}</x-nav-link>
                    @endif
                    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">{{ __('Notifications') }}</x-nav-link>
                </div>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            @if(in_array($role, ['super_admin', 'rcc_staff']))
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">{{ __('Clients') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('plots.index')" :active="request()->routeIs('plots.*') || request()->routeIs('burials.*') || request()->routeIs('deceased.*')">{{ __('Lots & Burials') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('burial-permits.index')" :active="request()->routeIs('burial-permits.*')">{{ __('Permits (AF 58)') }}</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*') || request()->routeIs('payments.*')">{{ __('Contracts & Billing') }}</x-responsive-nav-link>
            @if(in_array($role, ['super_admin', 'rcc_staff']))
            <div class="pt-2 pb-1">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Services</p>
            </div>
            <x-responsive-nav-link :href="route('pre-need-plans.index')" :active="request()->routeIs('pre-need-plans.*')">{{ __('Pre-Need Plans') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('columbary-niches.index')" :active="request()->routeIs('columbary-niches.*')">{{ __('Columbary Niches') }}</x-responsive-nav-link>
            @if($role === 'super_admin')
            <x-responsive-nav-link :href="route('burial-spots.index')" :active="request()->routeIs('burial-spots.*')">{{ __('Legacy Map') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('cemetery.admin')" :active="request()->routeIs('cemetery.*')">{{ __('Cemetery Map') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('paths.index')" :active="request()->routeIs('paths.*')">{{ __('Pathways') }}</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('inquiries.index')" :active="request()->routeIs('inquiries.*')">{{ __('Inquiries') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('client-notifications.index')" :active="request()->routeIs('client-notifications.*')">{{ __('Client Notifs') }}</x-responsive-nav-link>
            @endif
            @if($role === 'engr')
            <div class="pt-2 pb-1">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Map</p>
            </div>
            <x-responsive-nav-link :href="route('cemetery.admin')" :active="request()->routeIs('cemetery.*')">{{ __('Cemetery Polygons') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('plots.index')" :active="request()->routeIs('plots.*')">{{ __('Burial Plotting & Blocks') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('paths.index')" :active="request()->routeIs('paths.*')">{{ __('Map Pathing') }}</x-responsive-nav-link>
            @endif
            @if($role === 'super_admin')
            <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">{{ __('Activity Logs') }}</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">{{ __('Notifications') }}</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
