<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public $showPassword = false;
public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/');
        }

        $this->addError('email', 'Email atau password yang Anda masukkan salah.');
    }

    public function render()
    {
        return view('pages.login.⚡index')->layout('layouts.guest');
    }
};
?>
<div class="min-h-screen flex bg-base-100">
            <div class="flex flex-col justify-center items-center w-full lg:w-[35%] p-10 z-20 bg-base-100 shadow-2xl">
                <div class="w-full max-w-sm">

                    <div class="mb-10 text-center lg:text-left">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary text-primary-content mb-4 shadow-lg shadow-primary/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight uppercase">Login</h2>
                        <p class="text-base-content/60 text-sm mt-1 font-medium">Helpdesk Sentral Bahana Ekatama</p>
                    </div>

                    <form wire:submit="login" class="space-y-5">
    <div class="form-control w-full"> <label class="label pb-1">
            <span class="label-text font-bold text-xs uppercase opacity-70 tracking-wider">Email Address</span>
        </label>
        <input type="email"
               wire:model="email"
               placeholder="nama@perusahaan.com"
               class="input input-bordered w-full focus:input-primary @error('email') input-error @enderror"
               required />
        @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    <div class="form-control w-full"> <label class="label pb-1">
            <span class="label-text font-bold text-xs uppercase opacity-70 tracking-wider">Password</span>
        </label>
        <div class="relative w-full flex items-center"> <input type="{{ $showPassword ? 'text' : 'password' }}"
                   wire:model="password"
                   placeholder="••••••••"
                   class="input input-bordered w-full pr-12 focus:input-primary @error('password') input-error @enderror"
                   required />

            <button type="button"
                    wire:click="togglePassword"
                    class="absolute right-3 p-1 text-base-content/50 hover:text-primary transition-colors focus:outline-none">
                @if($showPassword)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                @endif
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block text-white shadow-lg">
        <span wire:loading.remove wire:target="login">Masuk ke Sistem</span>
        <span wire:loading wire:target="login" class="loading loading-spinner"></span>
    </button>
</form>

                    <div class="mt-20 text-center opacity-40">
                        <p class="text-[10px] tracking-widest font-bold uppercase italic">Authorized Personnel Only</p>
                    </div>
                </div>
            </div>

            <div class="hidden lg:flex lg:w-[65%] relative overflow-hidden bg-primary">
                <div class="absolute inset-0 bg-cover bg-center"
                     style="background-image: url('https://images.unsplash.com/photo-1513828583688-c52646db42da?q=80&w=2070&auto=format&fit=crop');">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/95 via-primary/60 to-transparent"></div>
                </div>

                <div class="relative z-10 flex flex-col justify-end p-20 text-white">
                    <div class="max-w-2xl">
                        <div class="badge badge-outline text-white/80 mb-6 p-4 font-bold tracking-widest uppercase">Maintenance System</div>
                        <h1 class="text-6xl font-black mb-6 leading-[1.1]">Pantau Aset secara <span class="text-secondary">Real-time.</span></h1>
                        <p class="text-xl text-white/70 leading-relaxed font-light">
                            Kelola form PPP secara digital, lacak downtime, dan tingkatkan efisiensi perbaikan departemen Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
