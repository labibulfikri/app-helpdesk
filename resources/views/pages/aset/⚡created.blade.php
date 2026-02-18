<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Aset;
use App\Models\Departement;
use App\Models\Categories;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads;

    // Properti Model
    public $nama_aset, $kategori_aset, $category_id, $lokasi_aset, $kondisi_aset = 'Bagus';
    public $nomor_serial, $tgl_pembelian, $nilai_perolehan, $status_aset = 'Tersedia';
    public $keterangan, $departement_id, $foto;

    public function saveAsset()
{
    $this->validate([
        'nama_aset' => 'required|min:3',
        'nomor_serial' => 'required|unique:aset,nomor_serial',
        'category_id' => 'nullable|exists:categories,id',
        'departement_id' => 'required',
        'tgl_pembelian' => 'required|date',
        'nilai_perolehan' => 'required|numeric',
        'foto' => 'nullable|image|max:2048',
    ]);

    $fotoPath = $this->foto ? $this->foto->store('aset', 'public') : null;

    // 1. Simpan aset terlebih dahulu tanpa QR Code untuk mendapatkan ID
    $aset = Aset::create([
        'nama_aset' => $this->nama_aset,
        'kategori_aset' => Categories::find($this->category_id)?->name ?? null,
        'category_id' => $this->category_id,
        'lokasi_aset' => $this->lokasi_aset,
        'kondisi_aset' => $this->kondisi_aset,
        'nomor_serial' => $this->nomor_serial,
        'tgl_pembelian' => $this->tgl_pembelian,
        'nilai_perolehan' => $this->nilai_perolehan,
        'status_aset' => $this->status_aset,
        'keterangan' => $this->keterangan,
        'departement_id' => $this->departement_id,
        'user_id' => auth()->id(),
        'foto' => $fotoPath,
        'qr_code'        => 'PENDING',
    ]);

    // 2. Generate URL Detail Aset
    // Gantilah 'aset.details' sesuai dengan name route halaman detail aset Anda
    $urlDetail = route('aset.show', $aset->id);

    // 3. Update kolom qr_code dengan URL tersebut
    $aset->update([
        'qr_code' => $urlDetail
    ]);

    session()->flash('success', 'Aset Berhasil Didaftarkan dengan QR Code!');
    return redirect()->route('aset.index');
}
    // public function saveAsset()
    // {
    //     $this->validate([
    //         'nama_aset' => 'required|min:3',
    //         'nomor_serial' => 'required|unique:aset,nomor_serial',
    //         // 'kategori_aset' => 'required',
    //         'category_id' => 'nullable|exists:categories,id',
    //         'departement_id' => 'required',
    //         'tgl_pembelian' => 'required|date',
    //         'nilai_perolehan' => 'required|numeric',
    //         'foto' => 'nullable|image|max:2048', // Max 2MB
    //     ]);

    //     $fotoPath = $this->foto ? $this->foto->store('aset', 'public') : null;

    //     Aset::create([
    //         'nama_aset' => $this->nama_aset,
    //         'kategori_aset' => $category = Categories::find($this->category_id)?->name ?? null,
    //         'category_id' => $this->category_id,
    //         'lokasi_aset' => $this->lokasi_aset,
    //         'kondisi_aset' => $this->kondisi_aset,
    //         'nomor_serial' => $this->nomor_serial,
    //         'tgl_pembelian' => $this->tgl_pembelian,
    //         'nilai_perolehan' => $this->nilai_perolehan,
    //         'status_aset' => $this->status_aset,
    //         'keterangan' => $this->keterangan,
    //         'departement_id' => $this->departement_id,
    //         'user_id' => auth()->id(),
    //         'qr_code' => 'QR-' . strtoupper(Str::random(12)),
    //         'foto' => $fotoPath,
    //     ]);

    //     session()->flash('success', 'Aset Berhasil Didaftarkan!');
    //     return redirect()->route('aset.index');
    // }

    public function render()
    {
        return view('pages.aset.⚡created', [
            'departements' => Departement::orderBy('name', 'asc')->get(),
            'categories' => Categories::orderBy('name', 'asc')->get(),
        ]);
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10">
    <div class="mb-10">
        <h2 class="text-4xl font-black text-base-content tracking-tight italic uppercase">
            Registrasi <span class="text-primary not-italic">Aset Baru</span>
        </h2>
        <p class="text-base-content/50 font-bold mt-1 uppercase tracking-[0.2em] text-[10px]">
            Input data inventaris ke dalam sistem SBE
        </p>
    </div>

    <form wire:submit.prevent="saveAsset" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-8">
    <div class="card bg-base-100 shadow-2xl border border-base-content/5 rounded-[2.5rem] overflow-hidden">
        <div class="card-body p-8 lg:p-12 space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Nama Aset</span>
                    </label>
                    <input type="text" wire:model="nama_aset" placeholder="Contoh: Laptop MacBook Pro"
                           class="input input-bordered input-lg bg-base-200/30 rounded-2xl font-bold focus:input-primary transition-all @error('nama_aset') input-error @enderror">
                    @error('nama_aset') <label class="label"><span class="label-text-alt text-error font-bold uppercase italic text-[10px]">{{ $message }}</span></label> @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Nomor Serial / IMEI</span>
                    </label>
                    <input type="text" wire:model="nomor_serial" placeholder="SN-8293xxxx"
                           class="input input-bordered input-lg bg-base-200/30 rounded-2xl font-bold focus:input-primary transition-all @error('nomor_serial') input-error @enderror">
                    @error('nomor_serial') <label class="label"><span class="label-text-alt text-error font-bold uppercase italic text-[10px]">{{ $message }}</span></label> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Kategori</span></label>
                    <select wire:model="category_id" class="select select-bordered select-lg bg-base-200/30 rounded-2xl font-bold focus:select-primary">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Departemen</span></label>
                    <select wire:model="departement_id" class="select select-bordered select-lg bg-base-200/30 rounded-2xl font-bold focus:select-primary">
                        <option value="">Pilih Unit</option>
                        @foreach($departements as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Lokasi Fisik</span></label>
                    <input type="text" wire:model="lokasi_aset" placeholder="Gudang A / Lt 2"
                           class="input input-bordered input-lg bg-base-200/30 rounded-2xl font-bold focus:input-primary">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Tanggal Perolehan</span></label>
                    <input type="date" wire:model="tgl_pembelian"
                           class="input input-bordered input-lg bg-base-200/30 rounded-2xl font-bold focus:input-primary">
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Nilai Aset (IDR)</span></label>
                    <div class="join w-full">
                        <span class="join-item btn btn-lg bg-base-300 border-none font-black text-primary no-animation">Rp</span>
                        <input type="number" wire:model="nilai_perolehan"
                               class="input input-bordered input-lg bg-base-200/30 join-item w-full font-bold focus:input-primary border-l-0">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Kondisi Awal</span></label>
                    <div class="bg-base-200/30 p-2 rounded-2xl flex gap-2 border border-base-content/5">
                        @foreach(['Bagus', 'Rusak Ringan', 'Rusak Berat'] as $kondisi)
                            <label class="flex-1 cursor-pointer group">
                                <input type="radio" wire:model="kondisi_aset" value="{{ $kondisi }}" class="peer hidden">
                                <div class="text-center py-3 rounded-xl font-bold text-[10px] uppercase transition-all
                                            peer-checked:bg-primary peer-checked:text-primary-content opacity-50 peer-checked:opacity-100 hover:bg-base-300">
                                    {{ $kondisi }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Status</span></label>
                    <select wire:model="status_aset" class="select select-bordered select-lg bg-base-200/30 rounded-2xl font-bold focus:select-primary">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Digunakan">Sedang Digunakan</option>
                        <option value="Dipinjam">Dipinjamkan</option>
                    </select>
                </div>
            </div>

            <div class="form-control w-full ">
                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-50 tracking-widest">Catatan Tambahan</span></label>
                <textarea wire:model="keterangan" placeholder="Informasi spesifikasi atau sejarah singkat aset..."
                          class="textarea textarea-bordered bg-base-200/30 rounded-3xl font-bold h-32 p-6 focus:textarea-primary"></textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between px-4">
        <a href="{{ route('aset.index') }}" wire:navigate class="btn btn-ghost rounded-2xl font-black uppercase text-[10px] tracking-widest opacity-50 hover:opacity-100">
            Batal
        </a>
        <button type="submit" class="btn btn-primary px-10 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-primary/30 group">
            Daftarkan Aset
            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </div>
</div>

        <div class="space-y-6">
            <div class="card bg-base-100/40 backdrop-blur-xl border border-base-content/5 shadow-2xl rounded-[3rem] p-8 sticky top-10">
                <h3 class="text-[10px] font-black uppercase opacity-40 mb-6 tracking-[0.3em]">Foto Dokumentasi</h3>

                <div class="relative group aspect-square rounded-[2.5rem] bg-base-content/5 border-2 border-dashed border-base-content/10 flex items-center justify-center overflow-hidden transition-all hover:border-primary">
                    @if($foto)
                        <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover animate-in zoom-in-75 duration-500">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <label class="cursor-pointer bg-white text-black font-black text-[10px] px-6 py-2 rounded-full uppercase">Ganti Foto</label>
                        </div>
                    @else
                        <div class="text-center group-hover:scale-110 transition-transform duration-300">
                            <div class="w-16 h-16 bg-primary/10 text-primary rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-[9px] font-black uppercase opacity-40 tracking-tighter">Upload Gambar Aset</p>
                            <p class="text-[8px] italic opacity-30 mt-1">Format: JPG, PNG (Max 2MB)</p>
                        </div>
                    @endif
                    <input type="file" wire:model="foto" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>

                <div wire:loading wire:target="foto" class="mt-4 p-4 rounded-2xl bg-primary/5 flex items-center gap-3">
                    <span class="loading loading-spinner loading-xs text-primary"></span>
                    <span class="text-[9px] font-black text-primary uppercase">Mengunggah Gambar...</span>
                </div>

                <div class="mt-8 p-6 rounded-[2rem] bg-base-content/5 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-primary shadow-[0_0_10px_rgba(var(--p),0.5)]"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest opacity-60">Sistem QR Code Otomatis</span>
                    </div>
                    <p class="text-[9px] leading-relaxed opacity-40 font-bold">
                        Sistem akan menggenerate QR Code unik secara otomatis setelah data aset disimpan untuk mempermudah inventarisasi fisik.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
