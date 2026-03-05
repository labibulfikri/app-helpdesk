<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Aset;
use App\Models\Departement;
use App\Models\Categories; // Tambahkan ini
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public Aset $aset;

    // Properti Form
    public $nama_aset, $category_id, $lokasi_aset, $kondisi_aset; // Ganti kategori_aset jadi category_id
    public $nomor_serial, $tgl_pembelian, $nilai_perolehan, $status_aset;
    public $keterangan, $departement_id, $foto, $old_foto; // Ganti departement_id jadi departement_id

    public function mount(Aset $aset)
    {
        $this->aset = $aset;
        $this->nama_aset = $aset->nama_aset;
        $this->category_id = $aset->category_id; // Sesuaikan field DB
        $this->lokasi_aset = $aset->lokasi_aset;
        $this->kondisi_aset = $aset->kondisi_aset;
        $this->nomor_serial = $aset->nomor_serial;
        $this->tgl_pembelian = $aset->tgl_pembelian;
        $this->nilai_perolehan = $aset->nilai_perolehan;
        $this->status_aset = $aset->status_aset;
        $this->keterangan = $aset->keterangan;
      $this->departement_id = (string) $aset->departement_id;
        $this->old_foto = $aset->foto;
    }

    public function updateAsset()
    {
        $this->validate([
            'nama_aset' => 'required|min:3',
            'nomor_serial' => 'required|unique:aset,nomor_serial,' . $this->aset->id,
            'category_id' => 'required',
            'departement_id' => 'required',
            'tgl_pembelian' => 'required|date',
            'nilai_perolehan' => 'required|numeric',
            'foto' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nama_aset' => $this->nama_aset,
            'category_id' => $this->category_id,
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
        return view('pages.aset.⚡edit', [ // Hapus emoji di nama file jika sudah diganti
            'categories' => Categories::orderBy('name', 'asc')->get(),
            // Daftar Kode PPP (Departemen)
            'departements' => Departement::orderBy('name', 'asc')->get(),
            'list_kondisi' => ['Bagus', 'Rusak Ringan', 'Rusak Berat'],

        ]);
    }
};
?>
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
        <a href="{{ route('aset.index') }}" wire:navigate class="btn btn-ghost btn-sm rounded-xl font-black uppercase text-[10px] tracking-widest opacity-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Batal
        </a>
    </div>

    <form wire:submit.prevent="updateAsset" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-base-100 border border-base-content/5 shadow-xl rounded-[2.5rem] overflow-hidden">
                <div class="p-8 lg:p-10 space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control w-full">
                            <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Nama Aset</label>
                            <input type="text" wire:model="nama_aset" class="input input-bordered bg-base-200/50 rounded-2xl font-bold focus:ring-2 ring-warning border-none">
                            @error('nama_aset') <span class="text-error text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control w-full">
                            <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Nomor Serial</label>
                            <input type="text" wire:model="nomor_serial" class="input input-bordered bg-base-200/50 rounded-2xl font-bold focus:ring-2 ring-warning border-none">
                            @error('nomor_serial') <span class="text-error text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control w-full">
                            <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Kategori</label>
                            <select wire:model="category_id" class="select select-bordered bg-base-200/50 rounded-2xl font-bold border-none">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-error text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control w-full">
                            <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Departemen </label>
                           <select wire:model="departement_id" class="select select-bordered bg-base-200/50 rounded-2xl font-bold border-none">
    <option value="">Pilih Departemen</option>
    @foreach($departements as $dept)
        {{-- Gunakan $dept->id jika itu adalah PK dari tabel departements --}}
        <option value="{{ $dept->id }}" {{ $departement_id == $dept->id ? 'selected' : '' }}>
            {{ $dept->name }}
        </option>
    @endforeach
</select>
                            @error('departement_id') <span class="text-error text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest mb-2">Kondisi Aset</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($list_kondisi as $kondisi)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model="kondisi_aset" value="{{ $kondisi }}" class="peer hidden">
                                    <div class="text-center py-4 rounded-2xl bg-base-200/50 font-black text-[10px] uppercase peer-checked:bg-warning peer-checked:text-warning-content transition-all border-2 border-transparent peer-checked:border-warning/20 opacity-60 peer-checked:opacity-100">
                                        {{ $kondisi }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('kondisi_aset') <span class="text-error text-[10px] font-bold mt-2">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label font-black text-[10px] uppercase opacity-40 tracking-widest">Catatan Tambahan</label>
                        <textarea wire:model="keterangan" class="textarea textarea-bordered bg-base-200/50 rounded-3xl font-bold h-32 p-6 border-none focus:ring-2 ring-warning" placeholder="Berikan keterangan detail mengenai aset..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn btn-warning btn-wide rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-warning/20">
                    <span wire:loading.remove wire:target="updateAsset">Update Data Aset</span>
                    <span wire:loading wire:target="updateAsset" class="loading loading-spinner"></span>
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 border border-base-content/5 shadow-xl rounded-[2.5rem] p-8 sticky top-10">
                <h3 class="text-[10px] font-black uppercase opacity-40 mb-6 tracking-[0.3em] text-center">Preview Foto</h3>

                <div class="relative group aspect-square rounded-[2rem] bg-base-200 border-2 border-dashed border-base-content/10 flex items-center justify-center overflow-hidden transition-all hover:border-warning/50">
                    @if($foto)
                        <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif($old_foto)
                        <img src="{{ asset('storage/' . $old_foto) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                    @else
                        <div class="flex flex-col items-center opacity-20">
                            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-[10px] font-black uppercase tracking-widest">No Image</span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="text-white font-black text-[10px] uppercase tracking-widest">Ganti Foto</span>
                    </div>

                    <input type="file" wire:model="foto" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                </div>

                <div class="mt-8 p-6 rounded-2xl bg-warning/5 border border-warning/10">
                    <div class="flex gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-[9px] leading-relaxed opacity-70 font-bold italic text-warning uppercase">
                            Mengunggah foto baru akan mengganti file lama di server secara otomatis.
                        </p>
                    </div>
                </div>

                @error('foto') <span class="text-error text-[10px] font-bold mt-4 block text-center uppercase">{{ $message }}</span> @enderror
            </div>
        </div>
    </form>
</div>
