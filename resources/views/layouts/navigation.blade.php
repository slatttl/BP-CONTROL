<nav x-data="{ open: false }" class="relative z-20 border-b border-slate-200/70 bg-white/90 backdrop-blur">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-700 text-white shadow-sm">
                                <x-application-logo class="h-6 w-6" />
                            </div>
                            <div>
                                <div class="text-sm font-semibold tracking-wide text-slate-900">BP Control</div>
                                <div class="text-xs text-slate-500">Posudky a exporty</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-3 sm:ms-10 sm:flex sm:items-center">
                    <x-nav-link :href="route('reviews.index')" :active="request()->routeIs('reviews.index') || request()->routeIs('reviews.show') || request()->routeIs('reviews.edit')">
                        {{ __('Posudky') }}
                    </x-nav-link>
                    <x-nav-link :href="route('reviews.create')" :active="request()->routeIs('reviews.create')">
                        {{ __('Nový posudok') }}
                    </x-nav-link>
                    @if (Auth::user()->isAdmin())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                            {{ __('Používatelia') }}
                        </x-nav-link>   
                        
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                @if (Auth::user()->isAdmin())
                    <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700">Admin</span>
                @endif
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-600 shadow-sm transition duration-150 ease-in-out hover:border-slate-300 hover:text-slate-800 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
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

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-1 px-3 pb-3 pt-2">
            <x-responsive-nav-link :href="route('reviews.index')" :active="request()->routeIs('reviews.index') || request()->routeIs('reviews.show') || request()->routeIs('reviews.edit')">
                {{ __('Posudky') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reviews.create')" :active="request()->routeIs('reviews.create')">
                {{ __('Nový posudok') }}
            </x-responsive-nav-link>
            @if (Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                    {{ __('Používatelia') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-slate-200 px-3 pb-3 pt-4">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                @if (Auth::user()->isAdmin())
                    <div class="mt-2 inline-flex rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700">Admin</div>
                @endif
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
