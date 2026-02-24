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
        $asset = Aset::findOrFail($id);

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
<div class="max-w-7xl mx-auto p-6 lg:p-10 space-y-6">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-base-200">
        <div>
            <h2 class="text-3xl font-bold text-base-content tracking-tight uppercase">
                Master Data <span class="text-primary">Aset</span>
            </h2>
            <p class="text-base-content/50 font-semibold mt-1 uppercase tracking-widest text-[10px] flex items-center gap-2">
                Manajemen Inventaris SBE • Total {{ Aset::count() }} Item
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" wire:model.live="search" placeholder="Cari aset..."
                       class="input input-bordered bg-base-100 rounded-xl pl-10 shadow-sm w-64 font-medium text-sm focus:border-primary">
                <svg class="w-4 h-4 absolute left-3.5 top-3.5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <a href="{{ route('aset.create') }}" wire:navigate
               class="btn btn-primary rounded-xl font-bold text-xs uppercase tracking-wider px-6">
                Tambah Aset
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success shadow-sm rounded-xl text-success-content font-bold uppercase text-[10px] tracking-wider">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200/50 text-base-content/60 font-bold uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="py-4">Informasi Aset</th>
                        <th>Kategori & Lokasi</th>
                        <th>Kondisi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">QR Code</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($assets as $asset)
                    <tr class="hover:bg-base-200/30 transition-colors">
                        <td class="py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-base-200 overflow-hidden border border-base-300">
                                    @if($asset->foto)
                                        <img src="{{ asset('storage/' . $asset->foto) }}" class="object-cover w-full h-full" />
                                    @else
                                        <div class="flex items-center justify-center h-full opacity-20">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-base-content uppercase tracking-tight">{{ $asset->nama_aset }}</div>
                                    <div class="text-[10px] text-primary font-mono font-bold">{{ $asset->nomor_serial }}</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="flex flex-col">
                                <span class="uppercase font-bold text-[9px] text-base-content/40 tracking-tighter">{{ $asset->kategori_aset }}</span>
                                <span class="text-[11px] font-medium opacity-70">{{ $asset->lokasi_aset }}</span>
                            </div>
                        </td>

                        <td>
                            <span class="px-2 py-1 rounded text-[9px] font-bold uppercase border {{ $asset->kondisi_aset == 'Bagus' ? 'border-success/30 text-success bg-success/5' : 'border-warning/30 text-warning bg-warning/5' }}">
                                {{ $asset->kondisi_aset }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="text-[9px] font-bold uppercase tracking-widest {{ $asset->status_aset == 'Tersedia' ? 'text-primary' : 'opacity-30' }}">
                                {{ $asset->status_aset }}
                            </span>
                        </td>

                        <td class="text-center">
                            @if($asset->qr_code)
                                <div class="inline-block p-1 bg-white border border-base-300 rounded-md">
                                    {!! QrCode::size(40)->margin(0)->generate($asset->qr_code) !!}
                                </div>
                            @else
                                <span class="text-[8px] uppercase opacity-20">No QR</span>
                            @endif
                        </td>

                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('aset.show', $asset->id) }}" wire:navigate class="btn btn-square btn-ghost btn-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('aset.edit', $asset->id) }}" wire:navigate class="btn btn-square btn-ghost btn-xs text-warning">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button onclick="confirmDelete({{ $asset->id }})" class="btn btn-square btn-ghost btn-xs text-error">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="flex flex-col items-center opacity-20">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p class="font-bold uppercase tracking-widest text-[10px]">Data tidak ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-base-200 bg-base-50">
            {{ $assets->links() }}
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Aset?',
            text: "Data yang dihapus tidak dapat dipulihkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'rounded-lg font-bold uppercase text-xs tracking-widest',
                cancelButton: 'rounded-lg font-bold uppercase text-xs tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteAsset', id);
            }
        })
    }
</script>
