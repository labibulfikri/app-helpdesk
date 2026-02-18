<?php
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User; // Sesuaikan dengan model user Anda
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithPagination;

    // State untuk Form
    public $name, $email, $role, $password, $employee_id;
    public $isEdit = false;
    public $search = '';

    // Reset pagination jika search berubah
    public function updatingSearch() {
        $this->resetPage();
    }

    public function with() {
        return [
            'employees' => User::where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%")
                            ->latest()
                            ->paginate(10)
        ];
    }

    public function resetFields() {
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->password = '';
        $this->employee_id = null;
        $this->isEdit = false;
    }

    public function save() {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->employee_id,
            'role' => 'required',
            'password' => $this->isEdit ? 'nullable|min:6' : 'required|min:6',
        ]);

        if ($this->isEdit) {
            $user = User::find($this->employee_id);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
            ]);
            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }
            session()->flash('success', 'Data Pegawai berhasil diperbarui!');
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'password' => Hash::make($this->password),
            ]);
            session()->flash('success', 'Pegawai baru berhasil ditambahkan!');
        }

        $this->resetFields();
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        $this->employee_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->isEdit = true;
    }

    public function delete($id) {
        User::destroy($id);
        session()->flash('success', 'Pegawai berhasil dihapus dari sistem!');
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="alert alert-success shadow-lg border-none rounded-2xl text-white font-bold animate-bounce-short">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">

        <div class="xl:col-span-4 sticky top-28">
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden transition-all">
                <div class="p-8 bg-[#020617] text-white flex justify-between items-center">
                    <h3 class="font-black italic uppercase tracking-tighter text-lg">
                        {{ $isEdit ? 'Update Data' : 'Tambah Pegawai' }}
                    </h3>
                    @if($isEdit)
                        <button wire:click="resetFields" class="btn btn-xs btn-circle btn-ghost text-slate-400">✕</button>
                    @endif
                </div>

                <form wire:submit.prevent="save" class="p-8 space-y-5">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-slate-600 uppercase text-[10px] tracking-widest">Nama Lengkap</span></label>
                        <input type="text" wire:model="name" class="input input-bordered rounded-xl bg-slate-50 focus:border-indigo-500 @error('name') input-error @enderror" placeholder="Contoh: Andi Saputra">
                        @error('name') <span class="text-error text-[10px] mt-1 font-bold italic uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-slate-600 uppercase text-[10px] tracking-widest">Email Perusahaan</span></label>
                        <input type="email" wire:model="email" class="input input-bordered rounded-xl bg-slate-50 focus:border-indigo-500 @error('email') input-error @enderror" placeholder="andi@sbe.co.id">
                        @error('email') <span class="text-error text-[10px] mt-1 font-bold italic uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-slate-600 uppercase text-[10px] tracking-widest">Role / Jabatan</span></label>
                        <select wire:model="role" class="select select-bordered rounded-xl bg-slate-50 focus:border-indigo-500 @error('role') select-error @enderror">
                            <option value="">Pilih Role...</option>
                            <option value="admin">Admin</option>
                            <option value="hrd">HRD</option>
                            <option value="dept_head">Department Head</option>
                            <option value="technician">Technician</option>
                            <option value="staff">Staff</option>
                        </select>
                        @error('role') <span class="text-error text-[10px] mt-1 font-bold italic uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-slate-600 uppercase text-[10px] tracking-widest">Kata Sandi {{ $isEdit ? '(Kosongkan jika tidak ganti)' : '' }}</span></label>
                        <input type="password" wire:model="password" class="input input-bordered rounded-xl bg-slate-50 focus:border-indigo-500 @error('password') input-error @enderror">
                        @error('password') <span class="text-error text-[10px] mt-1 font-bold italic uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary btn-block rounded-xl font-black shadow-lg shadow-indigo-500/30 uppercase tracking-widest">
                            {{ $isEdit ? 'Simpan Perubahan' : 'Daftarkan Pegawai' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="xl:col-span-8 space-y-6">
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-50/50">
                    <h3 class="font-black italic uppercase tracking-tighter text-xl text-slate-800">Direktori Pegawai</h3>
                    <div class="relative w-full md:w-72">
                        <input type="text" wire:model.live="search" class="input input-bordered w-full rounded-2xl pl-10 h-11 text-sm shadow-inner" placeholder="Cari nama atau email...">
                        <svg class="w-4 h-4 absolute left-4 top-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-lg">
                        <thead class="text-slate-400 uppercase text-[10px] font-black tracking-[0.2em] bg-white border-b border-slate-100">
                            <tr>
                                <th class="pl-8 py-5 text-center w-20">Foto</th>
                                <th>Informasi User</th>
                                <th>Role</th>
                                <th class="text-right pr-8">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-600 font-bold">
                            @forelse($employees as $row)
                            <tr class="hover:bg-slate-50 transition-all border-b border-slate-50 last:border-0 group">
                                <td class="pl-8 py-4 text-center">
                                    <div class="avatar shadow-md">
                                        <div class="w-10 rounded-xl ring-2 ring-indigo-500/10">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($row->name) }}&background=4f46e5&color=fff&bold=true" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-slate-900 font-black tracking-tight text-md">{{ $row->name }}</span>
                                        <span class="text-[11px] opacity-50 font-medium lowercase">{{ $row->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-outline border-slate-300 text-slate-500 font-black uppercase text-[9px] px-3 py-2.5 rounded-lg">{{ $row->role }}</span>
                                </td>
                                <td class="text-right pr-8">
                                    <div class="flex justify-end gap-1 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="edit({{ $row->id }})" class="btn btn-ghost btn-square btn-sm text-indigo-600 hover:bg-indigo-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button onclick="confirm('Hapus pegawai ini?') || event.stopImmediatePropagation()" wire:click="delete({{ $row->id }})" class="btn btn-ghost btn-square btn-sm text-rose-500 hover:bg-rose-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-20 italic opacity-40">Belum ada data pegawai yang terdaftar</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-slate-50/50 border-t border-slate-100">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
