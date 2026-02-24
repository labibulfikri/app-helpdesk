<?php
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User; // Sesuaikan dengan model user Anda
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithPagination;

    // State untuk Form
    public $name, $email, $role, $password, $employee_id, $jabatan;
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
        $this->jabatan = '';
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
                'jabatan' => $this->jabatan,
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
                'jabatan' => $this->jabatan,
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
        $this->jabatan = $user->jabatan;
        $this->isEdit = true;
    }

    public function deletePegawai($id) {
        // User::destroy($id);

        $user = User::findOrFail($id);
         $user->delete();
        session()->flash('success', 'Pegawai berhasil dihapus dari sistem!');
    }

        public function render() {
            return view('pages.pegawai.⚡index', [
                'employees' => User::where('name', 'like', "%{$this->search}%")
                                ->orWhere('email', 'like', "%{$this->search}%")
                                ->latest()
                                ->paginate(10)
            ]);
        }

}; ?>
<div class="max-w-7xl mx-auto p-6 lg:p-10 space-y-8">
    <div class="pb-6 border-b border-base-200">
        <h2 class="text-3xl font-bold text-base-content tracking-tight uppercase">
            Manajemen <span class="text-primary">Pegawai</span>
        </h2>
        <p class="text-base-content/50 font-bold mt-1 uppercase tracking-widest text-[10px] flex items-center gap-2">
            Direktori Karyawan SBE • Total {{ \App\Models\User::count() }} Personel
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
        <div class="xl:col-span-4 sticky top-28">
            <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
                <div class="p-6 bg-base-900 text-white flex justify-between items-center">
                    <h3 class="font-bold uppercase tracking-wider text-sm">
                        {{ $isEdit ? 'Update Data' : 'Tambah Pegawai' }}
                    </h3>
                    @if($isEdit)
                        <button wire:click="resetFields" class="btn btn-xs btn-circle btn-ghost text-white/50 hover:text-white">✕</button>
                    @endif
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-5">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-base-content/60 uppercase text-[10px] tracking-widest">Nama Lengkap</span></label>
                        <input type="text" wire:model="name" class="input input-bordered rounded-xl bg-base-50 focus:border-primary text-sm @error('name') input-error @enderror" placeholder="Andi Saputra">
                        @error('name') <span class="text-error text-[10px] mt-1 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-base-content/60 uppercase text-[10px] tracking-widest">Email Perusahaan</span></label>
                        <input type="email" wire:model="email" class="input input-bordered rounded-xl bg-base-50 focus:border-primary text-sm @error('email') input-error @enderror" placeholder="andi@sbe.co.id">
                        @error('email') <span class="text-error text-[10px] mt-1 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-base-content/60 uppercase text-[10px] tracking-widest">Role</span></label>
                        <select wire:model="role" class="select select-bordered rounded-xl bg-base-50 focus:border-primary text-sm @error('role') select-error @enderror">
                            <option value="">Pilih Role...</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin</option>
                            <option value="technician">Technician</option>
                            <option value="staff">Staff</option>
                        </select>
                        @error('role') <span class="text-error text-[10px] mt-1 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-base-content/60 uppercase text-[10px] tracking-widest">Jabatan</span></label>
                        <select wire:model="jabatan" class="select select-bordered rounded-xl bg-base-50 focus:border-primary text-sm @error('jabatan') select-error @enderror">
                            <option value="">Pilih Jabatan...</option>
                            <option value="Manager">Manager</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Teknisi">Teknisi</option>
                            <option value="Staff">Staff</option>
                        </select>
                        @error('jabatan') <span class="text-error text-[10px] mt-1 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-base-content/60 uppercase text-[10px] tracking-widest">Kata Sandi {{ $isEdit ? '(Opsi)' : '' }}</span></label>
                        <input type="password" wire:model="password" class="input input-bordered rounded-xl bg-base-50 focus:border-primary text-sm @error('password') input-error @enderror">
                        @error('password') <span class="text-error text-[10px] mt-1 font-bold uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary btn-block rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                            {{ $isEdit ? 'Simpan Perubahan' : 'Daftarkan Pegawai' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="xl:col-span-8 space-y-6">
            <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
                <div class="p-6 border-b border-base-200 flex flex-col md:flex-row justify-between items-center gap-4 bg-base-50/50">
                    <h3 class="font-bold uppercase tracking-tight text-lg text-base-content">Direktori Pegawai</h3>
                    <div class="relative w-full md:w-72">
                        <input type="text" wire:model.live="search" class="input input-bordered w-full rounded-xl pl-10 h-10 text-xs focus:border-primary" placeholder="Cari nama atau email...">
                        <svg class="w-4 h-4 absolute left-3.5 top-3 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-base-200/50 text-base-content/50 uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="pl-8 py-5 text-center w-24">Profil</th>
                                <th>Informasi User</th>
                                <th>Role</th>
                                <th class="text-right pr-8">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-base-content">
                            @forelse($employees as $row)
                            <tr class="hover:bg-base-200/30 transition-colors">
                                <td class="pl-8 py-4">
                                    <div class="avatar flex justify-center">
                                        <div class="w-10 rounded-lg border border-base-300">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($row->name) }}&background=4f46e5&color=fff&bold=true&rounded=false" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-base-content font-bold tracking-tight text-sm uppercase">{{ $row->name }}</span>
                                        <span class="text-[10px] opacity-50 font-medium">{{ $row->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-md border border-base-300 text-base-content/60 font-bold uppercase text-[9px] tracking-wider bg-base-100">
                                        {{ $row->role }}
                                    </span>
                                </td>
                                <td class="text-right pr-8">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="edit({{ $row->id }})" class="btn btn-square btn-ghost btn-xs text-primary hover:bg-primary/10">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button onclick="confirmDeleteEmployee({{ $row->id }})" class="btn btn-square btn-ghost btn-xs text-error hover:bg-error/10">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-20 opacity-30">
                                    <p class="font-bold uppercase tracking-widest text-xs text-base-content">Data tidak ditemukan</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-base-50/50 border-t border-base-200">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteEmployee(id) {
        Swal.fire({
            title: 'Hapus Pegawai?',
            text: "Akses user ini akan dicabut permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'rounded-lg font-bold uppercase text-[10px] tracking-widest px-6',
                cancelButton: 'rounded-lg font-bold uppercase text-[10px] tracking-widest px-6'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deletePegawai', id);
            }
        })
    }
</script>
