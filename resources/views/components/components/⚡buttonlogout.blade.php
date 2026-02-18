<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function logout()
    {
        Auth::logout();

        // Hancurkan session agar tidak bisa di-back browser
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/login');
    }
};
?>

<div>
  <button wire:click="logout" class="flex items-center gap-4 w-full px-4 py-3 rounded-2xl text-rose-500 hover:bg-rose-500/10 transition-all group">
        <svg class="w-6 h-6 flex-shrink-0 group-hover:-translate-x-1 transition-transform font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
        </svg>
        <span x-show="sidebarOpen" class="font-black italic uppercase text-xs tracking-widest">Logout System</span>
    </button>
</div>
