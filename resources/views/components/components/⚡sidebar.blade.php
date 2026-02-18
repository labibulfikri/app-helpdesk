<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
<div>
    <aside
        :class="{ 'w-72': sidebarOpen, 'w-20': !sidebarOpen, '-translate-x-full': !mobileSidebar, 'translate-x-0': mobileSidebar }"
        class="sidebar-transition fixed inset-y-0 left-0 z-[70]
               bg-base-100/60 backdrop-blur-xl lg:static lg:translate-x-0
               border-r border-base-content/5 flex flex-col h-full
               shadow-2xl overflow-hidden transition-all duration-300">

        <div class="h-20 flex-shrink-0 flex items-center px-6 border-b border-base-content/5">
            <div class="flex items-center gap-3">
                <div class="btn btn-primary btn-square rounded-2xl shadow-lg shadow-primary/20 no-animation">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="leading-none">
                    <span class="text-base-content font-black text-xl tracking-tighter uppercase italic">Help<span class="text-primary not-italic tracking-normal"> Desk</span></span>
                    <span class="block text-[8px] font-black opacity-40 uppercase tracking-[0.2em] mt-1 whitespace-nowrap"> SENTRAL BAHANA EKATAMA </span>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto sidebar-scrollbar">

            <div x-show="sidebarOpen" class="px-4 mb-3 text-[10px] font-black opacity-30 uppercase tracking-[0.3em]">Main Menu</div>

            <a href="/" wire:navigate
               class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
               {{ request()->routeIs('dashboard')
                  ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                  : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
               }}">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7m7 7l2 2m-2 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7l2-2m14 0l-2-2m-2 2l-2-2" />
                    </svg>
                </div>
                <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Dashboard</span>
            </a>

            <div x-show="sidebarOpen" class="px-4 pt-6 pb-3 text-[10px] font-black opacity-30 uppercase tracking-[0.3em]">Personal</div>

            <a href="/tickets" wire:navigate
               class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
               {{ request()->is('tickets*')
                  ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                  : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
               }}">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Permohonan Tiket</span>
            </a>

            @if(in_array(auth()->user()?->role, ['staff', 'technician', 'admin', 'hrd']))
                <a href="/profile" wire:navigate
                   class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
                   {{ request()->is('profile*')
                      ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                      : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
                   }}">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Profil Saya</span>
                </a>

            @endif

            @if(in_array(auth()->user()?->role, ['admin', 'hrd']))
                <div x-show="sidebarOpen" class="px-4 pt-6 pb-3 text-[10px] font-black opacity-30 uppercase tracking-[0.3em]">Management</div>

                <a href="/departement" wire:navigate
                   class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
                   {{ request()->is('departement*')
                      ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                      : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
                   }}">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Departemen</span>
                </a>

                <a href="/pegawai" wire:navigate
                   class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
                   {{ request()->is('pegawai*')
                      ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                      : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
                   }}">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Pegawai</span>
                </a>

                <div x-show="sidebarOpen" class="px-4 pt-6 pb-3 text-[10px] font-black opacity-30 uppercase tracking-[0.3em]">Category</div>

                <a href="/categories" wire:navigate
                   class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
                   {{ request()->is('categories*')
                      ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                      : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
                   }}">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Master Category</span>
                </a>
                <a href="/aset" wire:navigate
                   class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300
                   {{ request()->is('aset*')
                      ? 'bg-primary text-primary-content shadow-xl shadow-primary/20'
                      : 'text-base-content/60 hover:bg-primary/10 hover:text-primary'
                   }}">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-black text-[11px] uppercase tracking-[0.15em] whitespace-nowrap">Master Aset</span>
                </a>
            @endif



        </nav>

        <div class="p-4 flex-shrink-0 border-t border-base-content/5 bg-transparent">
            <div :class="sidebarOpen ? 'px-2' : 'flex justify-center'">
                 @livewire('components.buttonlogout')
            </div>
        </div>
    </aside>
</div>
