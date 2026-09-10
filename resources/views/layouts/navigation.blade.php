<nav class="relative z-20 border-b border-slate-200/70 bg-white/90 backdrop-blur">
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

            <!-- Settings Links -->
            <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                @if (Auth::user()->isAdmin())
                    <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700">Admin</span>
                @endif
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-800">
                    {{ Auth::user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-800">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div class="border-t border-slate-200 sm:hidden">
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

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-800">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
