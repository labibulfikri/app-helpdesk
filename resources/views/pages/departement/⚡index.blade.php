<?php

namespace App\Livewire\Departement;

use App\Models\Departement;
use Livewire\Component;
use Livewire\WithPagination;

new class  extends Component
{
    use WithPagination;

    // Public Properties untuk Form
    public $name, $code, $description, $selected_id;
    public $search = '';
    public $isEdit = false;

    protected $rules = [
        'code' => 'required|unique:departements,code',
        'name' => 'required|min:3',
    ];

    // Fungsi Simpan
    public function store()
    {
        $this->validate();

        Departement::create([
            'code'        => strtoupper($this->code),
            'name'        => $this->name,
            'description' => $this->description,
        ]);

        $this->resetFields();
        session()->flash('success', 'Departemen berhasil ditambahkan.');
    }

    // Fungsi Load Data untuk Edit
    public function edit($id)
    {
        $departement = Departement::findOrFail($id);
        $this->selected_id = $id;
        $this->code        = $departement->code;
        $this->name        = $departement->name;
        $this->description = $departement->description;
        $this->isEdit      = true;
    }

    // Fungsi Update
    public function update()
    {
        $this->validate([
            'code' => 'required|unique:departements,code,' . $this->selected_id,
            'name' => 'required|min:3',
        ]);

        $departement = Departement::find($this->selected_id);
        $departement->update([
            'code'        => strtoupper($this->code),
            'name'        => $this->name,
            'description' => $this->description,
        ]);

        $this->resetFields();
        session()->flash('success', 'Data berhasil diperbarui.');
    }

    // Fungsi Hapus
    public function delete($id)
    {
        Departement::destroy($id);
        session()->flash('success', 'Departemen telah dihapus.');
    }

    // Reset Form
    public function resetFields()
    {
        $this->reset(['name', 'code', 'description', 'selected_id', 'isEdit']);
    }

    public function render()
    {
        return view('pages.departement.⚡index', [
            'departements' => Departement::where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('code', 'like', '%'.$this->search.'%')
                            ->latest()->paginate(10)
        ]);
    }
}
?>
<div class="max-w-[1400px] mx-auto p-4 lg:p-8">

    @if (session()->has('success'))
        <div class="alert alert-success shadow-lg mb-6 border-none text-white font-bold uppercase text-[10px] tracking-widest italic">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <div class="lg:col-span-5 sticky top-8">
            <div class="card bg-base-100 border border-base-300 shadow-xl overflow-hidden">
                <div class="bg-neutral text-neutral-content px-6 py-4 flex justify-between items-center">
                    <div class="flex flex-col">
                        <h2 class="text-[11px] font-black uppercase tracking-[0.2em] italic">
                            {{ $isEdit ? 'Update Data' : 'Tambah Baru' }}
                        </h2>
                        <span class="text-[9px] opacity-60 font-bold uppercase tracking-tighter">Formulir Departemen</span>
                    </div>
                    @if($isEdit)
                        <button wire:click="resetFields" class="btn btn-xs btn-circle btn-ghost">✕</button>
                    @endif
                </div>

                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="card-body p-8 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black uppercase tracking-widest opacity-60">1. Kode Departemen</label>
                        <input wire:model="code" type="text"
                            class="input input-bordered w-full font-mono uppercase focus:input-primary transition-all @error('code') border-error @enderror"
                            placeholder="MTC"/>
                        @error('code') <span class="text-[10px] text-error font-bold italic uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black uppercase tracking-widest opacity-60">2. Nama Departemen</label>
                        <input wire:model="name" type="text"
                            class="input input-bordered w-full font-bold focus:input-primary transition-all @error('name') border-error @enderror"
                            placeholder="Maintenance"/>
                        @error('name') <span class="text-[10px] text-error font-bold italic uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black uppercase tracking-widest opacity-60">3. Keterangan Singkat</label>
                        <textarea wire:model="description"
                            class="textarea textarea-bordered h-24 focus:textarea-primary leading-relaxed font-medium"
                            placeholder="Opsional..."></textarea>
                    </div>

                    <div class="card-actions flex flex-col gap-3 mt-4">
                        <button type="submit" class="btn btn-primary w-full font-black uppercase tracking-[0.2em] shadow-lg shadow-primary/20">
                            {{ $isEdit ? 'Simpan Perubahan' : 'Daftarkan Departemen' }}
                        </button>

                        @if($isEdit)
                            <button type="button" wire:click="resetFields" class="btn btn-ghost w-full font-black uppercase text-[10px] tracking-widest opacity-40">
                                Batalkan Pengeditan
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="mt-6 flex gap-4 p-4 rounded-2xl bg-base-200/50 border border-base-300">
                <div class="text-primary mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="text-[10px] font-bold opacity-60 uppercase leading-relaxed tracking-tight">
                    Pastikan kode departemen unik dan sesuai dengan standarisasi penomoran SBE.
                </div>
            </div>
        </div>

        <div class="lg:col-span-7">
            <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden min-h-[600px]">
                <div class="bg-base-200/50 px-6 py-4 border-b border-base-300 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-6 bg-primary rounded-full"></div>
                        <h2 class="text-[11px] font-black uppercase tracking-[0.2em] opacity-70">Database Departemen</h2>
                    </div>

                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-3 flex items-center opacity-30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input wire:model.live="search" type="text"
                            class="input input-bordered input-sm w-full pl-10 focus:input-primary font-bold text-xs"
                            placeholder="Cari Departemen..."/>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-base-100/50 border-b border-base-200">
                                <th class="w-24 py-5 px-6">Kode</th>
                                <th>Nama Departemen</th>
                                <th class="text-right px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($departements as $dept)
                            <tr class="hover:bg-base-200/40 transition-colors group">
                                <td class="px-6 py-4 font-mono text-primary font-black italic text-xs tracking-tighter">
                                    {{ $dept->code }}
                                </td>
                                <td class="font-bold text-base-content/80 group-hover:text-base-content transition-colors">
                                    {{ $dept->name }}
                                </td>
                                <td class="text-right px-6">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="edit({{ $dept->id }})"
                                            class="btn btn-ghost btn-xs text-info hover:bg-info/10 font-black uppercase tracking-tighter no-animation">
                                            Edit
                                        </button>
                                        <button onclick="confirm('Hapus departemen ini?') || event.stopImmediatePropagation()"
                                            wire:click="delete({{ $dept->id }})"
                                            class="btn btn-ghost btn-xs text-error hover:bg-error/10 font-black uppercase tracking-tighter no-animation">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-20">
                                    <div class="flex flex-col items-center opacity-20">
                                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        <span class="font-black uppercase tracking-widest text-xs">Data Tidak Ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-base-300 bg-base-100">
                    {{ $departements->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
