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
        'qr_code'        => 'PENDING', // Placeholder sementara
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
 <div class="min-h-screen bg-base-200/50 py-12 px-4"> <div class="max-w-7xl mx-auto">

        {{-- Header Section --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-5xl font-black text-base-content tracking-tighter italic uppercase leading-none">
                    Registrasi <span class="text-primary not-italic">Aset Baru</span>
                </h2>
                <p class="text-base-content/50 font-bold mt-3 uppercase tracking-[0.3em] text-[10px] flex items-center gap-2">
                    <span class="w-10 h-[2px] bg-primary"></span>
                    Input data inventaris ke dalam sistem SBE
                </p>
            </div>
            <div class="hidden md:block text-right">
                <span class="text-[10px] font-black opacity-30 uppercase tracking-widest">Database Server: Active</span>
            </div>
        </div>

        <form wire:submit.prevent="saveAsset" class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

            {{-- KOLOM KIRI: FORM DATA (Span 8) --}}
            <div class="lg:col-span-8 space-y-8">
                <div class="card bg-base-100 shadow-sm border border-base-200 rounded-[3rem] overflow-hidden">
                    <div class="card-body p-8 lg:p-14 space-y-10">

                        {{-- Baris 1: Nama & Serial --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">1. Identitas Nama Aset</span>
                                </label>
                                <input type="text" wire:model="nama_aset" placeholder="Contoh: MacBook Pro 14 M3"
                                       class="input input-bordered rounded-2xl font-bold bg-base-200/20 h-14 focus:ring-4 ring-primary/10 transition-all">
                                @error('nama_aset') <label class="label"><span class="label-text-alt text-error font-bold uppercase italic text-[10px]">{{ $message }}</span></label> @enderror
                            </div>

                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">2. Nomor Serial / IMEI</span>
                                </label>
                                <input type="text" wire:model="nomor_serial" placeholder="SN-8293xxxx"
                                       class="input input-bordered rounded-2xl font-bold bg-base-200/20 h-14 focus:ring-4 ring-primary/10 transition-all">
                                @error('nomor_serial') <label class="label"><span class="label-text-alt text-error font-bold uppercase italic text-[10px]">{{ $message }}</span></label> @enderror
                            </div>
                        </div>

                        {{-- Baris 2: Kategori, Dept, Lokasi --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Kategori</span></label>
                                <select wire:model="category_id" class="select select-bordered rounded-2xl font-bold bg-base-200/20 h-14">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Departemen</span></label>
                                <select wire:model="departement_id" class="select select-bordered rounded-2xl font-bold bg-base-200/20 h-14">
                                    <option value="">Pilih Unit</option>
                                    @foreach($departements as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Lokasi Fisik</span></label>
                                <input type="text" wire:model="lokasi_aset" placeholder="Gudang A / Lt 2"
                                       class="input input-bordered rounded-2xl font-bold bg-base-200/20 h-14">
                            </div>
                        </div>

                        {{-- Baris 3: Tanggal & Nilai --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Tanggal Perolehan</span></label>
                                <input type="date" wire:model="tgl_pembelian"
                                       class="input input-bordered rounded-2xl font-bold bg-base-200/20 h-14">
                            </div>

                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Nilai Perolehan</span></label>
                                <div class="join w-full shadow-sm rounded-2xl overflow-hidden border border-base-300">
                                    <span class="join-item bg-base-300 px-6 flex items-center font-black text-primary text-xs">IDR</span>
                                    <input type="number" wire:model="nilai_perolehan"
                                           class="input input-ghost join-item w-full font-bold focus:bg-transparent h-14 bg-base-200/10">
                                </div>
                            </div>
                        </div>

                        {{-- Baris 4: Kondisi & Status --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Kondisi Awal</span></label>
                                <div class="bg-base-200/40 p-2 rounded-2xl flex gap-2 border border-base-300">
                                    @foreach(['Bagus', 'Rusak Ringan', 'Rusak Berat'] as $kondisi)
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" wire:model="kondisi_aset" value="{{ $kondisi }}" class="peer hidden">
                                            <div class="text-center py-3 rounded-xl font-bold text-[10px] uppercase transition-all
                                                        peer-checked:bg-primary peer-checked:text-primary-content opacity-40 peer-checked:opacity-100 hover:bg-base-300">
                                                {{ $kondisi }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Status Inventaris</span></label>
                                <select wire:model="status_aset" class="select select-bordered rounded-2xl font-bold bg-base-200/20 h-14">
                                    <option value="Tersedia">✅ Tersedia</option>
                                    <option value="Digunakan">👤 Sedang Digunakan</option>
                                    <option value="Dipinjam">🔄 Dipinjamkan</option>
                                </select>
                            </div>
                        </div>

                        {{-- Baris 5: Catatan --}}
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-black text-[10px] uppercase opacity-40 tracking-[0.2em]">Spesifikasi & Keterangan</span></label>
                            <textarea wire:model="keterangan" placeholder="Informasi spesifikasi teknis atau sejarah singkat aset..."
                                      class="textarea textarea-bordered bg-base-200/20 rounded-[2rem] font-bold h-32 p-6 focus:ring-4 ring-primary/10"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between px-6">
                    <a href="{{ route('aset.index') }}" wire:navigate class="btn btn-ghost rounded-2xl font-black uppercase text-[10px] tracking-widest opacity-50 hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-12 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-2xl shadow-primary/30 group">
                        Simpan Data Aset
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            {{-- KOLOM KANAN: MEDIA & QR (Span 4) --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="card bg-base-100 shadow-sm border border-base-200 rounded-[3rem] p-8 sticky top-10">
                    <h3 class="text-[10px] font-black uppercase opacity-40 mb-6 tracking-[0.3em] flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Dokumentasi Visual
                    </h3>

                    {{-- Upload Area --}}
                    <div class="relative group aspect-square rounded-[2.5rem] bg-base-200/50 border-2 border-dashed border-base-300 flex items-center justify-center overflow-hidden transition-all hover:border-primary hover:bg-base-100">
                        @if($foto)
                            <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                <label class="cursor-pointer bg-white text-black font-black text-[10px] px-8 py-3 rounded-full uppercase tracking-widest shadow-xl">Ganti Foto</label>
                            </div>
                        @else
                            <div class="text-center group-hover:scale-105 transition-transform duration-500">
                                <div class="w-20 h-20 bg-primary/10 text-primary rounded-[2rem] flex items-center justify-center mx-auto mb-5">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <p class="text-[10px] font-black uppercase opacity-40 tracking-widest">Ambil Gambar</p>
                                <p class="text-[8px] italic opacity-30 mt-2">Format: JPG, PNG (Max 2MB)</p>
                            </div>
                        @endif
                        <input type="file" wire:model="foto" class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>

                    {{-- Loading Indicator --}}
                    <div wire:loading wire:target="foto" class="mt-4 p-4 rounded-2xl bg-primary text-primary-content flex items-center justify-center gap-3 animate-pulse">
                        <span class="loading loading-spinner loading-xs"></span>
                        <span class="text-[9px] font-black uppercase tracking-widest">Processing Image...</span>
                    </div>

                    {{-- QR Info --}}
                    <div class="mt-10 p-8 rounded-[2.5rem] bg-neutral text-neutral-content relative overflow-hidden group">
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-2 h-2 rounded-full bg-success animate-ping"></div>
                                <span class="text-[9px] font-black uppercase tracking-widest opacity-80">Auto-Generate System</span>
                            </div>
                            <h4 class="font-black text-sm mb-2 uppercase tracking-tight">QR Asset Code</h4>
                            <p class="text-[10px] leading-relaxed opacity-60 font-medium">
                                Sistem akan mencetak label QR Code unik secara otomatis untuk ditempelkan pada fisik aset setelah penyimpanan berhasil.
                            </p>
                        </div>
                        {{-- Background Decoration --}}
                        <svg class="absolute -right-4 -bottom-4 w-24 h-24 opacity-10 group-hover:rotate-12 transition-transform duration-700" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h6v6h-6v-6zM5 5v2h2V5H5zm12 0v2h2V5h-12zm-12 10v2h2v-2H5zm12 0v2h2v-2h-12zM9 3v2h2v-2H9zm0 4v2h2V7H9zm4-4v2h2V3h-2zm0 4v2h2V7h-2zm-4 4v2h2v-2H9zm0 4v2h2v-2H9zm4-4v2h2v-2h-2zm0 4v2h2v-2h-2zm-4-4v2h2v-2H9z"/></svg>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
