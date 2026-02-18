<?php

use Livewire\Component;
use App\Models\Categories; // Pastikan Model Category sudah dibuat
use Illuminate\Support\Str;

new class extends Component {
    // Properti Form
    public $name;
    public $deskripsi;
    public $editId = null;

    // Reset form
    public function resetFields()
    {
        $this->name = '';
        $this->editId = null;
    }

    // Simpan atau Update
    public function save()
    {
        $this->validate([
            'name' => 'required|min:3|unique:categories,name,' . $this->editId,
        ]);

        Categories::updateOrCreate(
            ['id' => $this->editId],
            [
                'name' => $this->name,
                'slug' => Str::slug($this->name),
            ]
        );

        $this->resetFields();
        session()->flash('success', $this->editId ? 'Kategori diperbarui.' : 'Kategori ditambah.');
    }

    // Load data ke form untuk edit
    public function edit($id)
    {
        $category = Categories::findOrFail($id);
        $this->editId = $category->id;
        $this->name = $category->name;
    }

    // Hapus data
    public function delete($id)
    {
        Categories::destroy($id);
        session()->flash('success', 'Kategori dihapus.');
    }

    public function render()
    {
        return view('pages.category.⚡index', [
            'categories' => Categories::latest()->get()
        ]);
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10 space-y-8">
    <div class="flex flex-col md:flex-row gap-8">

        <div class="w-full md:w-1/3">
            <div class="card bg-base-100 shadow-2xl rounded-[2.5rem] overflow-hidden border border-base-content/5">
                <div class="bg-base-content p-6">
                    <h3 class="text-white font-black uppercase italic tracking-tighter text-lg">
                        {{ $editId ? 'Edit Kategori' : 'Tambah Kategori' }}
                    </h3>
                </div>
                <form wire:submit.prevent="save" class="p-8 space-y-5">
                    <div class="form-control w-full">
                        <label class="label font-black text-[10px] uppercase opacity-40">Nama Kategori</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Elektronik"
                               class="input input-bordered rounded-2xl font-bold focus:ring-2 ring-primary">
                        @error('name') <span class="text-error text-[10px] font-bold mt-1 italic">{{ $message }}</span> @enderror
                    </div>


                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="btn btn-primary flex-1 rounded-xl font-black uppercase text-xs italic">
                            {{ $editId ? 'Update' : 'Daftarkan' }}
                        </button>
                        @if($editId)
                            <button type="button" wire:click="resetFields" class="btn btn-ghost rounded-xl font-black uppercase text-xs">Batal</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full md:w-2/3">
            <div class="card bg-white/60 backdrop-blur-xl shadow-2xl rounded-[2.5rem] border border-white/20 p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black uppercase italic tracking-tighter text-base-content">
                        Direktori <span class="text-primary">Kategori</span>
                    </h3>
                </div>

                @if (session()->has('success'))
                    <div class="alert alert-success py-2 rounded-xl mb-4 text-white font-bold text-xs uppercase italic">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="table w-full border-separate border-spacing-y-2">
                        <thead class="text-base-content/40 font-black uppercase text-[10px] tracking-widest">
                            <tr>
                                <th class="bg-transparent border-none">Nama Kategori</th>
                                <th class="bg-transparent border-none text-right">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="font-bold">
                            @forelse($categories as $category)
                                <tr class="bg-base-100/50 hover:bg-white transition-all shadow-sm rounded-2xl group">
                                    <td class="rounded-l-2xl py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs">
                                                {{ substr($category->name, 0, 1) }}
                                            </div>
                                            <span class="uppercase tracking-tight">{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td class="rounded-r-2xl text-right">
                                        <div class="flex justify-end gap-1">
                                            <button wire:click="edit({{ $category->id }})" class="btn btn-ghost btn-xs text-primary hover:bg-primary/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button onclick="confirm('Hapus kategori ini?') || event.stopImmediatePropagation()"
                                                    wire:click="delete({{ $category->id }})" class="btn btn-ghost btn-xs text-error hover:bg-error/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-10 opacity-20 italic text-xs uppercase font-black tracking-widest">Data Kosong</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
