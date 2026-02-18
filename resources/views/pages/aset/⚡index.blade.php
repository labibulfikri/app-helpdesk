<?php

use Livewire\Component;
use App\Models\Aset;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination;

    public $search = '';

    // Reset pagination saat pencarian berubah agar tidak stuck di page tinggi
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteAsset($id)
    {
        $asset = Asset::findOrFail($id);

        if ($asset->foto) {
            Storage::disk('public')->delete($asset->foto);
        }

        $asset->delete();
        session()->flash('success', 'Aset berhasil dihapus.');
    }

    public function render()
    {
        return view('pages.aset.⚡index', [
            'assets' => Aset::query()
                ->where('nama_aset', 'like', '%' . $this->search . '%')
                ->orWhere('nomor_serial', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10)
        ]);
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-4xl font-black text-base-content tracking-tight italic uppercase">
                Master Data <span class="text-primary not-italic">Aset</span>
            </h2>
            <p class="text-base-content/50 font-bold mt-1 uppercase tracking-[0.2em] text-[10px] flex items-center gap-2">
                <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                Manajemen Inventaris SBE • Total {{  Aset::count() }} Item
            </p>
        </div>

        <div class="flex gap-3">
            <div class="relative group">
                <input type="text" wire:model.live="search" placeholder="Cari Aset..."
                       class="input bg-base-100/60 backdrop-blur-md border-none rounded-2xl pl-12 shadow-sm focus:ring-2 ring-primary w-64 font-bold text-sm">
                <svg class="w-5 h-5 absolute left-4 top-3.5 opacity-30 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <a href="{{ route('aset.create') }}" wire:navigate
               class="btn btn-primary rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl shadow-primary/30">
                + Tambah Aset
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success shadow-lg rounded-2xl border-none text-white font-bold italic uppercase text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-base-100/40 backdrop-blur-xl rounded-[3rem] shadow-2xl border border-base-content/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full border-separate border-spacing-y-3 px-6">
                <thead class="text-base-content/40 font-black uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="bg-transparent border-none">Informasi Aset</th>
                        <th class="bg-transparent border-none">Kategori & Lokasi</th>
                        <th class="bg-transparent border-none">Kondisi</th>
                        <th class="bg-transparent border-none text-center">Status</th>
                        <th class="bg-transparent border-none text-center">QR</th>
                        <th class="bg-transparent border-none text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-base-content font-bold">
                    @forelse($assets as $asset)
                    <tr class="hover:shadow-lg transition-all rounded-3xl overflow-hidden group border-none">
                        <td class="bg-base-100/80 group-hover:bg-base-100 border-none rounded-l-[2rem] py-4">
                            <div class="flex items-center gap-4">
                                <div class="avatar">
                                    <div class="w-14 h-14 rounded-2xl shadow-inner bg-base-200 overflow-hidden ring-2 ring-primary/10">
                                        @if($asset->foto)
                                            <img src="{{ asset('storage/' . $asset->foto) }}" class="object-cover" />
                                        @else
                                            <div class="flex items-center justify-center h-full text-base-content/20">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-black uppercase tracking-tight">{{ $asset->nama_aset }}</div>
                                    <div class="text-[10px] text-primary italic font-black uppercase tracking-tighter">{{ $asset->nomor_serial }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="bg-base-100/80 group-hover:bg-base-100 border-none text-xs">
                            <div class="flex flex-col gap-1">
                                <span class="uppercase tracking-widest font-black opacity-30 text-[9px]">{{ $asset->kategori_aset }}</span>
                                <span class="flex items-center gap-1 opacity-70 uppercase">
                                    <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $asset->lokasi_aset }}
                                </span>
                            </div>
                        </td>

                        <td class="bg-base-100/80 group-hover:bg-base-100 border-none">
                            <div class="badge badge-outline font-black text-[9px] uppercase px-3 py-3 italic border-2 rounded-xl {{ $asset->kondisi_aset == 'Bagus' ? 'badge-success' : 'badge-warning' }}">
                                {{ $asset->kondisi_aset }}
                            </div>
                        </td>

                        <td class="bg-base-100/80 group-hover:bg-base-100 border-none text-center">
                            <span class="badge {{ $asset->status_aset == 'Tersedia' ? 'badge-primary' : 'badge-ghost opacity-40' }} font-black rounded-lg uppercase text-[8px] py-3 px-4 shadow-sm border-none italic tracking-widest">
                                {{ $asset->status_aset }}
                            </span>
                        </td>


                        <td class="bg-base-100/80 group-hover:bg-base-100 border-none text-center">
                            @if($asset->qr_code)
                                <div {!! QrCode::size(150)->margin(1)->generate($asset->qr_code) !!}</div>
                            @else
                                <span class="text-[9px] italic uppercase tracking-widest opacity-30">QR Code tidak tersedia</span>
                            @endif
                        </td>

                        <td class="bg-base-100/80 group-hover:bg-base-100 border-none text-right rounded-r-[2rem] pr-6">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('aset.show', $asset->id) }}" wire:navigate class="btn btn-square btn-ghost btn-sm text-primary hover:bg-primary/10 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('aset.edit', $asset->id) }}" wire:navigate class="btn btn-square btn-ghost btn-sm text-warning hover:bg-warning/10 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                <button onclick="confirm('Yakin hapus aset?') || event.stopImmediatePropagation()" wire:click="deleteAsset({{ $asset->id }})" class="btn btn-square btn-ghost btn-sm text-error hover:bg-error/10 rounded-xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center">
                            <div class="opacity-20 flex flex-col items-center">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p class="font-black italic uppercase tracking-widest text-xs">Belum ada aset terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-base-content/5 bg-base-100/30">
            {{ $assets->links() }}
        </div>
    </div>
</div>
