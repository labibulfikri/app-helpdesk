<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component {
    use WithFileUploads;

    public $name,   $email, $foto, $old_foto;
    // Properti Password
    public $current_password, $new_password, $new_password_confirmation;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->old_foto = $user->foto;
    }


    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => 'required|confirmed|min:8',
            // 'new_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.'
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('success', 'Password berhasil diperbarui!');
        $this->dispatch('close-modal-password'); // Menutup modal setelah berhasil
    }

    public function updateProfile()
    {
        $user = auth()->user();
        $this->validate([
            'name' => 'required|min:3',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'foto' => 'nullable|image|max:1024',
        ]);

        $data = ['name' => $this->name,  'email' => $this->email];

        if ($this->foto) {
            if ($user->foto) Storage::disk('public')->delete($user->foto);
            $data['foto'] = $this->foto->store('profile-photos', 'public');
        }

        $user->update($data);
        $this->old_foto = $user->foto;
        $this->foto = null;
        session()->flash('success', 'Profil berhasil diperbarui!');
    }
}; ?>

<div class="max-w-6xl mx-auto p-6 lg:p-12">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="h-1 w-12 bg-primary rounded-full"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-primary">Account Management</span>
            </div>
            <h2 class="text-2xl font-black text-base-content tracking-tighter italic uppercase">
                My Profile
            </h2>
        </div>
        <div class="flex gap-2">
            <div class="stats bg-base-100 shadow-sm border border-base-content/5 rounded-2xl px-4 py-1">
                <div class="stat p-0 flex flex-col items-end">
                    <div class="stat-title text-[8px] font-black uppercase opacity-40">Role Access</div>
                    <div class="stat-value text-xs font-black uppercase text-primary italic">{{ auth()->user()->role ?? 'Staff' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success shadow-2xl rounded-2xl mb-10 border-none text-white font-bold italic uppercase text-[10px] tracking-widest animate-bounce">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <div class="lg:col-span-4 space-y-6">
            <div class="card bg-base-100 shadow-2xl rounded-[3rem] border border-base-content/5 p-10 text-center relative overflow-hidden group">
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none italic font-black text-6xl break-words leading-none select-none">
                    PROFILE PROFILE PROFILE PROFILE
                </div>

                <div class="relative z-10">
                    <div class="relative inline-block group">
                        <div class="avatar">
                            <div class="w-44 h-44 rounded-[2.5rem] ring-[12px] ring-base-200 group-hover:ring-primary/10 transition-all duration-500 overflow-hidden shadow-inner">
                                @if ($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="object-cover" />
                                @elseif ($old_foto)
                                    <img src="{{ asset('storage/' . $old_foto) }}" class="object-cover" />
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=641ae6&color=fff&size=200" />
                                @endif
                            </div>
                        </div>

                        <label class="absolute bottom-2 right-2 btn btn-primary btn-circle btn-sm shadow-xl scale-0 group-hover:scale-100 transition-transform duration-300 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <input type="file" wire:model="foto" class="hidden">
                        </label>
                    </div>

                    <div class="mt-8">
                        <h3 class="font-black text-xl uppercase italic leading-none">{{ $name }}</h3>
                        {{-- <p class="text-[10px] font-bold opacity-30 uppercase tracking-[0.3em] mt-2">{{ '@'.$username }}</p> --}}
                    </div>

                    <div wire:loading wire:target="foto" class="mt-4">
                        <span class="loading loading-infinity loading-md text-primary"></span>
                    </div>
                </div>
            </div>

            <div class="card bg-primary p-8 rounded-[2.5rem] text-primary-content shadow-xl shadow-primary/20 relative overflow-hidden">
    <div class="relative z-10">
        <h4 class="font-black italic uppercase text-sm mb-1 text-white">Keamanan Akun</h4>
        <p class="text-[9px] font-bold opacity-70 uppercase leading-relaxed mb-4 text-white">Pastikan password Anda diperbarui secara berkala.</p>

        <label for="modal_password" class="btn btn-xs bg-white/20 border-none text-white font-black uppercase text-[9px] rounded-lg hover:bg-white/40">
            Ganti Password
        </label>
    </div>
    <svg class="absolute -right-4 -bottom-4 w-24 h-24 opacity-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
</div>
 <input type="checkbox" id="modal_password" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle backdrop-blur-md transition-all">
    <div class="modal-box bg-base-100 rounded-[3rem] p-0 border border-base-content/5 shadow-2xl   max-w-lg">

        <div class="bg-primary p-8 text-primary-content relative">
            <label for="modal_password" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 text-white">✕</label>
            <h3 class="font-black text-2xl uppercase italic tracking-tighter">Update <span class="text-white/50 not-italic">Security</span></h3>
            <p class="text-[10px] font-bold opacity-70 uppercase tracking-[0.2em] mt-1">Ubah kata sandi akun anda</p>
        </div>

        <form wire:submit.prevent="updatePassword" class="p-10 space-y-8">

            <div class="form-control w-full group">
                <label class="label pb-1">
                    <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest group-focus-within:text-primary transition-colors">Password Saat Ini</span>
                </label>
                <div class="relative">
                    <input type="password" wire:model="current_password" placeholder="••••••••"
                           class="input input-lg w-full bg-base-200/50 border-none rounded-2xl font-bold focus:ring-4 ring-primary/10 transition-all text-sm">
                </div>
                @error('current_password') <span class="text-error text-[9px] font-bold mt-2 uppercase italic">{{ $message }}</span> @enderror
            </div>

            <div class="divider opacity-5 my-0"></div>

            <div class="grid grid-cols-1 gap-6">
                <div class="form-control w-full group">
                    <label class="label pb-1">
                        <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest group-focus-within:text-primary transition-colors">Password Baru</span>
                    </label>
                    <input type="password" wire:model="new_password" placeholder="Min. 8 Karakter"
                           class="input input-lg w-full bg-base-200/50 border-none rounded-2xl font-bold focus:ring-4 ring-primary/10 transition-all text-sm">
                    @error('new_password') <span class="text-error text-[9px] font-bold mt-2 uppercase italic">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full group">
                    <label class="label pb-1">
                        <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest group-focus-within:text-primary transition-colors">Konfirmasi Password</span>
                    </label>
                    <input type="password" wire:model="new_password_confirmation" placeholder="Ulangi password baru"
                           class="input input-lg w-full bg-base-200/50 border-none rounded-2xl font-bold focus:ring-4 ring-primary/10 transition-all text-sm">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="btn btn-primary w-full h-16 rounded-2xl font-black uppercase text-xs tracking-[0.3em] shadow-2xl shadow-primary/30 hover:scale-[1.02] active:scale-95 transition-all">

                    <span wire:loading.remove wire:target="updateProfile">Update Profile</span>

    <span wire:loading wire:target="updateProfile" class="flex items-center gap-2">
        <span class="loading loading-spinner loading-xs"></span>
        Processing...
    </span>
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>
        </div>

        <div class="lg:col-span-8">
            <div class="card bg-base-100 shadow-2xl rounded-[3rem] border border-base-content/5 overflow-hidden">
                <div class="card-body p-8 lg:p-14">
                    <form wire:submit.prevent="updateProfile" enctype="multipart/form-data" class="space-y-8">

                        {{-- <div class="form-control w-full group">
                            <label class="label pb-1">
                                <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em] group-focus-within:text-primary transition-colors">Nama Lengkap</span>
                            </label>
                            <input type="text" wire:model="name" placeholder="John Doe"
                                   class="input input-lg bg-base-200/50 border-none rounded-2xl font-bold focus:ring-4 ring-primary/10 transition-all text-sm">
                            @error('name') <span class="text-error text-[9px] font-bold mt-2 uppercase italic tracking-widest">{{ $message }}</span> @enderror
                        </div> --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="form-control w-full group">
                                <label class="label pb-1">
                                    <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em] group-focus-within:text-primary transition-colors">Name</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-4 text-xs opacity-20 font-black">@</span>
                                    <input type="text" wire:model="name" placeholder="johndoe"
                                           class="input input-lg bg-base-200/50 border-none rounded-2xl font-bold focus:ring-4 ring-primary/10 transition-all w-full pl-8 text-sm">
                                </div>
                                @error('name') <span class="text-error text-[9px] font-bold mt-2 uppercase italic tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control w-full group">
                                <label class="label pb-1">
                                    <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em] group-focus-within:text-primary transition-colors">E-Mail Unit</span>
                                </label>
                                <input type="email" wire:model="email"
                                       class="input input-lg bg-base-200/50 border-none rounded-2xl font-bold focus:ring-4 ring-primary/10 transition-all text-sm">
                                @error('email') <span class="text-error text-[9px] font-bold mt-2 uppercase italic tracking-widest">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex gap-4 p-5 rounded-3xl bg-base-200/30 border border-base-content/5 items-start mt-4">
                            <div class="bg-primary/10 p-2 rounded-xl text-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-[10px] font-bold opacity-50 italic leading-relaxed">
                                Perubahan data email akan berpengaruh pada proses login dan notifikasi tiket bantuan. Pastikan email masih aktif.
                            </p>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary px-14 rounded-2xl font-black uppercase text-xs tracking-[0.3em] shadow-2xl shadow-primary/40 hover:scale-105 transition-all group">
                                <span>Update Profile</span>
                                <svg class="w-4 h-4 ml-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-outline {
        -webkit-text-stroke: 1px currentColor;
        color: transparent;
    }
</style>
<script>
    window.addEventListener('close-modal-password', event => {
        // ID ini harus sama dengan ID pada <input type="checkbox" id="modal_password" ...>
        const modalCheckbox = document.getElementById('modal_password');
        if (modalCheckbox) {
            modalCheckbox.checked = false;
        }
    });
</script>
