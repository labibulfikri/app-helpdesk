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

<div class="max-w-7xl mx-auto p-6 lg:p-10 space-y-8">
    <div class="pb-6 border-b border-base-200">
        <h2 class="text-3xl font-bold text-base-content tracking-tight uppercase">
            Manajemen <span class="text-primary">Kategori</span>
        </h2>
        <p class="text-base-content/50 font-bold mt-1 uppercase tracking-widest text-[10px]">
            Pengaturan klasifikasi keluhan layanan helpdesk
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8 items-start">
        <div class="w-full lg:w-1/3 sticky top-28">
            <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden rounded-2xl">
                <div class="bg-base-200/50 p-6 border-b border-base-200">
                    <h3 class="text-base-content font-bold uppercase tracking-wider text-sm">
                        {{ $editId ? 'Update Kategori' : 'Tambah Kategori' }}
                    </h3>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-5">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-bold text-base-content/60 uppercase text-[10px] tracking-widest">Nama Kategori</span>
                        </label>
                        <input type="text" wire:model="name" placeholder="Elektronik, Jaringan, dsb..."
                               class="input input-bordered rounded-xl font-medium focus:border-primary text-sm bg-base-50">
                        @error('name')
                            <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit" class="btn btn-primary w-full rounded-xl font-bold uppercase text-xs tracking-widest shadow-lg shadow-primary/20">
                            {{ $editId ? 'Simpan Perubahan' : 'Daftarkan Kategori' }}
                        </button>

                        @if($editId)
                            <button type="button" wire:click="resetFields" class="btn btn-ghost w-full rounded-xl font-bold uppercase text-xs tracking-widest">
                                Batal
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full lg:w-2/3">
            <div class="card bg-base-100 shadow-sm border border-base-200 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-base-200 bg-base-50/50">
                    <h3 class="text-lg font-bold uppercase tracking-tight text-base-content">
                        Direktori <span class="text-primary">Kategori</span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-base-200/50 text-base-content/50 uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="pl-8 py-5">Nama Kategori</th>
                                <th class="text-right pr-8">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-base-content">
                            @forelse($categories as $category)
                                <tr class="hover:bg-base-200/30 transition-colors group">
                                    <td class="pl-8 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-sm border border-primary/20 uppercase">
                                                {{ substr($category->name, 0, 1) }}
                                            </div>
                                            <span class="font-bold text-sm uppercase tracking-tight">{{ $category->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-right pr-8">
                                        <div class="flex justify-end gap-1">
                                            <button wire:click="edit({{ $category->id }})"
                                                    class="btn btn-square btn-ghost btn-xs text-primary hover:bg-primary/10"
                                                    title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>

                                            <button onclick="confirmDeleteCategory({{ $category->id }})"
                                                    class="btn btn-square btn-ghost btn-xs text-error hover:bg-error/10"
                                                    title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-20 opacity-30">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="font-bold uppercase tracking-[0.3em] text-xs">Data Kosong</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteCategory(id) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: "Pastikan tidak ada tiket yang menggunakan kategori ini.",
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
                @this.call('delete', id);
            }
        })
    }
</script>
