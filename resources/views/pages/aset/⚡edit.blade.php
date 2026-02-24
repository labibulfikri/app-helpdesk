<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Aset;
use App\Models\Departement;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public Aset $aset;

    // Properti Form
    public $nama_aset, $kategori_aset, $lokasi_aset, $kondisi_aset;
    public $nomor_serial, $tgl_pembelian, $nilai_perolehan, $status_aset;
    public $keterangan, $departement_id, $foto, $old_foto;

    public function mount(Aset $aset)
    {
        $this->aset = $aset;
        $this->nama_aset = $aset->nama_aset;
        $this->kategori_aset = $aset->kategori_aset;
        $this->lokasi_aset = $aset->lokasi_aset;
        $this->kondisi_aset = $aset->kondisi_aset;
        $this->nomor_serial = $aset->nomor_serial;
        $this->tgl_pembelian = $aset->tgl_pembelian;
        $this->nilai_perolehan = $aset->nilai_perolehan;
        $this->status_aset = $aset->status_aset;
        $this->keterangan = $aset->keterangan;
        $this->departement_id = $aset->departement_id;
        $this->old_foto = $aset->foto;
    }

    public function updateAsset()
    {
        $this->validate([
            'nama_aset' => 'required|min:3',
            'nomor_serial' => 'required|unique:aset,nomor_serial,' . $this->aset->id,
            'kategori_aset' => 'required',
            'departement_id' => 'required',
            'tgl_pembelian' => 'required|date',
            'nilai_perolehan' => 'required|numeric',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nama_aset' => $this->nama_aset,
            'kategori_aset' => $this->kategori_aset,
            'lokasi_aset' => $this->lokasi_aset,
            'kondisi_aset' => $this->kondisi_aset,
            'nomor_serial' => $this->nomor_serial,
            'tgl_pembelian' => $this->tgl_pembelian,
            'nilai_perolehan' => $this->nilai_perolehan,
            'status_aset' => $this->status_aset,
            'keterangan' => $this->keterangan,
            'departement_id' => $this->departement_id,
        ];

        if ($this->foto) {
            // Hapus foto lama jika ada
            if ($this->old_foto) {
                Storage::disk('public')->delete($this->old_foto);
            }
            $data['foto'] = $this->foto->store('aset', 'public');
        }

        $this->aset->update($data);

        session()->flash('success', 'Data aset berhasil diperbarui!');
        return redirect()->route('aset.index');
    }

    public function render()
    {
        return view('pages.aset.⚡edit', [
            'departements' => Departement::orderBy('name', 'asc')->get()
        ]);
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10">
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-4xl font-black text-base-content tracking-tight italic uppercase">
                Edit <span class="text-warning not-italic">Aset</span>

            </h2>
            <p class="text-base-content/50 font-bold mt-1 uppercase tracking-[0.2em] text-[10px]">
                Memperbarui informasi: {{ $aset->nama_aset }}
            </p>
        </div>
        <a href="{{ route('aset.index') }}" wire:navigate class="btn btn-ghost rounded-2xl font-black uppercase text-[10px] tracking-widest opacity-50">
            Batal
        </a>
    </div>

    <form wire:submit.prevent="updateAsset" class="grid grid-cols-1 lg:grid-cols-3 gap-8" enctype="multipart/form-data">

        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100/40 backdrop-blur-xl border border-base-content/5 shadow-2xl rounded-[3rem] p-8 lg:p-12">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Nama Aset</label>
                        <input type="text" wire:model="nama_aset" class="input input-lg bg-base-200/50 border-none rounded-2xl font-bold focus:ring-2 ring-warning">
                    </div>
                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Nomor Serial</label>
                        <input type="text" wire:model="nomor_serial" class="input input-lg bg-base-200/50 border-none rounded-2xl font-bold focus:ring-2 ring-warning">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Kategori</label>
                        <select wire:model="kategori_aset" class="select select-lg bg-base-200/50 border-none rounded-2xl font-bold">
                            <option value="Elektronik">Elektronik</option>
                            <option value="Alat Kantor">Alat Kantor</option>
                            <option value="Mesin Produksi">Mesin Produksi</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Departemen</label>
                        <select wire:model="departement_id" class="select select-lg bg-base-200/50 border-none rounded-2xl font-bold">
                            @foreach($departements as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Status</label>
                        <select wire:model="status_aset" class="select select-lg bg-base-200/50 border-none rounded-2xl font-bold">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Digunakan">Digunakan</option>
                            <option value="Rusak">Rusak</option>
                        </select>
                    </div>
                </div>

                <div class="form-control mb-8">
                    <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Kondisi</label>
                    <div class="flex gap-4">
                        @foreach(['Bagus', 'Rusak Ringan', 'Rusak Berat'] as $kondisi)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model="kondisi_aset" value="{{ $kondisi }}" class="peer hidden">
                                <div class="text-center p-3 rounded-xl bg-base-200/50 font-bold text-[10px] uppercase peer-checked:bg-warning peer-checked:text-warning-content transition-all opacity-60 peer-checked:opacity-100">
                                    {{ $kondisi }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-control">
                    <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Catatan</label>
                    <textarea wire:model="keterangan" class="textarea bg-base-200/50 border-none rounded-3xl font-bold h-32 p-6"></textarea>
                </div>
            </div>

            <div class="flex justify-end px-8">
                <button type="submit" class="btn btn-warning px-12 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-2xl shadow-warning/40">
                    Simpan Perubahan
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100/40 backdrop-blur-xl border border-base-content/5 shadow-2xl rounded-[3rem] p-8 sticky top-10">
                <h3 class="text-[10px] font-black uppercase opacity-40 mb-6 tracking-[0.3em]">Update Foto</h3>

                <div class="relative group aspect-square rounded-[2.5rem] bg-base-content/5 border-2 border-dashed border-base-content/10 flex items-center justify-center overflow-hidden transition-all hover:border-warning">
                    @if($foto)
                        <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif($old_foto)
                        <img src="{{ asset('storage/' . $old_foto) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                    @else
                        <div class="text-center opacity-20">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <input type="file" wire:model="foto" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>

                <div class="mt-8 p-6 rounded-[2rem] bg-warning/5 border border-warning/10">
                    <p class="text-[9px] leading-relaxed opacity-60 font-bold italic text-warning">
                        * Mengunggah foto baru akan menghapus foto lama secara permanen dari server.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
