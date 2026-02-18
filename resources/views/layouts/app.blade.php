<!DOCTYPE html>
<html lang="id" data-theme="cyberpunk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SBE Helpdesk' }}</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.01em;
        }

        /* Menggunakan warna dari sistem DaisyUI untuk scrollbar */
        .sidebar-scrollbar::-webkit-scrollbar { width: 3px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--bc) / 0.1); border-radius: 20px; }

        .main-scrollbar::-webkit-scrollbar { width: 6px; }
        .main-scrollbar::-webkit-scrollbar-track { background: hsl(var(--b2)); }
        .main-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--bc) / 0.2); border-radius: 20px; }

        [x-cloak] { display: none !important; }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Glass effect menggunakan variabel DaisyUI */
        .glass-nav {
            background: hsl(var(--b1) / 0.8);
            backdrop-filter: blur(15px);
        }

        /* Table Styling menggunakan standar DaisyUI */
        .table { @apply text-[13px]; }
        .table thead tr { @apply border-b border-base-300 bg-base-200/50 text-[11px] uppercase tracking-wider font-extrabold; }
    </style>
</head>
<body class="h-screen overflow-hidden" x-data="{ sidebarOpen: true, mobileSidebar: false }">

    <div class="flex h-screen overflow-hidden">

        @livewire('components.sidebar')

        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

            <header class="h-20 flex-shrink-0 glass-nav glass  border-b border-base-300 flex items-center justify-between px-6 lg:px-10 z-50">
                <div class="flex items-center gap-4 lg:gap-6">
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex btn btn-ghost btn-sm btn-circle text-base-content/60">
                        <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" /></svg>
                        <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    <button @click="mobileSidebar = true" class="lg:hidden btn btn-ghost btn-sm btn-circle bg-base-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    <div class="flex flex-col">
                        <h1 class="text-base-content font-black text-lg lg:text-xl tracking-tighter uppercase italic leading-none">
                            {{ $title ?? 'System Monitoring' }}
                        </h1>
                        <div class="text-[9px] font-bold opacity-50 uppercase tracking-[0.2em] mt-1">SBE Management Panel</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 lg:gap-6">
                    @livewire('components.notification-bell')

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost h-auto py-2 px-2 lg:px-4 rounded-2xl flex items-center gap-3">
                            <div class="flex flex-col items-end hidden md:flex">
                                <span class="text-xs font-black text-base-content leading-none">{{ auth()->user()?->name ?? 'Guest' }}</span>
                                <span class="text-[9px] font-bold opacity-50 mt-1 uppercase tracking-tighter">{{ auth()->user()?->role ?? 'N/A' }}</span>
                            </div>
                            <div class="avatar">
                                <div class="w-9 lg:w-10 rounded-xl ring ring-primary ring-offset-base-100 ring-offset-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'G') }}&background=random&color=fff&bold=true" />
                                </div>
                            </div>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[100] menu p-3 shadow-2xl bg-base-100 rounded-2xl w-64 border border-base-300 mt-4">
                            <li class="menu-title text-[10px] font-black opacity-40 uppercase px-4 pt-2">Profil Saya</li>
                            <li><a wire:navigate href="/profile" class="py-3 rounded-xl font-bold gap-3 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg> Pengaturan Profil</a></li>
                            <div class="divider my-1"></div>
                            <li class="p-0">
                                @livewire('components.buttonlogout')
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto main-scrollbar  p-4 lg:p-10">
                <div class="max-w-[1400px] mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
