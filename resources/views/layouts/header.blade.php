<!-- Header -->
<header class="bg-white dark:bg-gray-800 border-b border-slate-200 dark:border-gray-700 sticky top-0 z-30 h-16 flex-shrink-0">
    <div class="h-full px-6 flex items-center justify-between">
        
        <!-- Left Side -->
        <div class="flex items-center gap-4">
            <button @click="toggleSidebar()" class="w-10 h-10 flex items-center justify-center text-slate-500 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            
            <!-- Breadcrumb / Title -->
            <div class="hidden md:block">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-wide">@yield('page_title', 'Inventory')</h2>
            </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-4">

            <!-- ECN Notifications -->
            @if(isset($ecnNotificationCount) && $ecnNotificationCount > 0)
            <button @click="$dispatch('open-ecn-alert')"
                class="relative w-10 h-10 flex items-center justify-center text-slate-400 dark:text-gray-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors"
                title="ECN Updated Alerts">
                <i class="fa-solid fa-bell text-xl text-red-500"></i>
                <span class="absolute top-2 right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-4 w-4 bg-red-600 text-[10px] font-bold text-white items-center justify-center">{{ $ecnNotificationCount }}</span>
                </span>
            </button>
            @endif


            <!-- Theme Toggle -->
            <button x-data="{ 
                        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        toggleTheme() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('theme', 'dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('theme', 'light');
                            }
                        }
                    }" 
                    @click="toggleTheme()" 
                    class="w-10 h-10 flex items-center justify-center text-slate-400 dark:text-gray-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors"
                    title="Toggle Dark Mode">
                <i class="fa-solid fa-sun text-xl" x-show="!darkMode"></i>
                <i class="fa-solid fa-moon text-xl" x-show="darkMode" style="display: none;"></i>
            </button>

            <!-- User Menu -->
            @auth
            <div x-data="{ open: false }" class="relative pl-4 border-l border-slate-200 dark:border-gray-700">
                
                <button @click="open = !open" @click.outside="open = false" 
                    class="flex items-center gap-3 hover:bg-primary-50 dark:hover:bg-gray-700 p-1.5 pr-3 transition-colors border border-transparent hover:border-slate-100 dark:hover:border-gray-600">
                    <div class="h-9 w-9 rounded-full bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-gray-400 flex items-center justify-center border border-slate-200 dark:border-gray-600">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-slate-700 dark:text-gray-200 leading-none">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-1">{{ Auth::user()->roles->pluck('name')->join(', ') ?: 'User' }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 dark:text-gray-500 ml-1 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open" x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-slate-100 dark:border-gray-700 py-1 shadow-lg" style="display: none;">
                    
                    <div class="px-4 py-3 border-b border-slate-50 dark:border-gray-700 md:hidden">
                        <p class="text-sm font-semibold text-slate-800 dark:text-gray-200">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ Auth::user()->roles->pluck('name')->join(', ') ?: 'User' }}</p>
                    </div>

                    <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-2">
                        <i class="fa-regular fa-user w-4"></i> Profile
                    </a>
                    
                    <a href="#" class="block px-4 py-2 text-sm text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-2">
                        <i class="fa-solid fa-gear w-4"></i> Settings
                    </a>
                    
                    <div class="border-t border-slate-50 dark:border-gray-700 my-1"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="pl-4 border-l border-slate-200 dark:border-gray-700">
                <a href="{{ route('login') }}" class="text-sm font-medium text-primary-600 hover:underline">Login</a>
            </div>
            @endauth

        </div>
    </div>
</header>
