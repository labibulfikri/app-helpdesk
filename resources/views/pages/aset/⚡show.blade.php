<?php

use Livewire\Component;
use App\Models\Aset;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

new class extends Component {
    public Asset $asset;

    public function mount($id)
    {
        // Load asset beserta relasi tickets, departemen, dan user (pelapor ticket)
        $this->aset = Aset::with(['tickets.user', 'departement'])->findOrFail($id);
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-10">
    <div class="flex items-center justify-between mb-10">
        <a href="{{ route('aset.index') }}" wire:navigate class="btn btn-ghost rounded-2xl gap-3 font-black uppercase text-[10px] tracking-widest opacity-50 hover:opacity-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <div class="flex gap-3">
            <a href="{{ route('aset.edit', $this->aset->id) }}" wire:navigate class="btn btn-warning rounded-2xl font-black text-[10px] uppercase italic px-8 shadow-xl shadow-warning/20">
                Edit Unit
            </a>
            <button class="btn btn-primary rounded-2xl font-black text-[10px] uppercase italic px-8 shadow-xl shadow-primary/20">
                Cetak QR
            </button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-8">

        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="card bg-base-100 shadow-2xl rounded-[3rem] overflow-hidden border border-base-content/5">
                <div class="aspect-square relative group">
                    @if($this->aset->foto)
                        <img src="{{ asset('storage/' . $this->aset->foto) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-base-200 flex items-center justify-center text-base-content/10">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <div class="absolute bottom-6 right-6">
                        <div class="bg-white p-3 rounded-2xl shadow-2xl border border-base-content/5">
                            {!! QrCode::size(60)->generate($this->aset->qr_code) !!}
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-primary font-black italic uppercase text-[8px] py-3">{{ $this->aset->kategori_aset }}</span>
                        <span class="badge badge-outline font-black italic uppercase text-[8px] py-3 border-2">{{ $this->aset->kondisi_aset }}</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tighter uppercase italic text-base-content leading-none mb-1">{{ $this->aset->nama_aset }}</h1>
                    <p class="text-primary font-bold text-xs tracking-[0.2em] mb-6 uppercase">{{ $this->aset->nomor_serial }}</p>

                    <div class="divider opacity-5"></div>

                    <div class="space-y-4 mt-6">
                        <div class="flex justify-between">
                            <span class="text-[10px] font-black uppercase opacity-30">Lokasi</span>
                            <span class="text-sm font-bold uppercase">{{ $this->aset->lokasi_aset }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] font-black uppercase opacity-30">Departemen</span>
                            <span class="text-sm font-bold uppercase">{{ $this->aset->departement->nama_departemen ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] font-black uppercase opacity-30">Tgl Perolehan</span>
                            <span class="text-sm font-bold uppercase">{{ \Carbon\Carbon::parse($this->aset->tgl_pembelian)->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] font-black uppercase opacity-30">Nilai Aset</span>
                            <span class="text-sm font-bold text-primary italic uppercase">Rp {{ number_format($this->aset->nilai_perolehan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-primary/5 rounded-[2.5rem] p-8 border border-primary/10">
                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40 mb-4">Catatan/Keterangan</h3>
                <p class="text-xs font-bold leading-relaxed opacity-70 italic">
                    {{ $this->aset->keterangan ?? 'Tidak ada catatan tambahan untuk aset ini.' }}
                </p>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8">
            <div class="card bg-white/60 backdrop-blur-xl shadow-2xl rounded-[3rem] p-10 border border-white/20 h-full">
                <div class="flex items-center justify-between mb-10">
                    <h3 class="text-2xl font-black tracking-tighter uppercase italic">Repair <span class="text-primary">History</span></h3>
                    <div class="text-[10px] font-black opacity-30 uppercase tracking-[0.2em]">Total {{ $this->aset->tickets->count() }} Laporan</div>
                </div>

                @if($this->aset->tickets->count() > 0)
                    <div class="space-y-8 relative before:absolute before:inset-y-0 before:left-[19px] before:w-0.5 before:bg-base-content/5">
                        @foreach($this->aset->tickets as $ticket)
                            <div class="relative pl-12">
                                <div class="absolute left-0 top-1 w-10 h-10 rounded-2xl bg-white shadow-lg flex items-center justify-center z-10 border border-base-content/5">
                                    <div class="w-3 h-3 rounded-full {{ $ticket->status == 'closed' ? 'bg-success' : 'bg-warning animate-pulse' }}"></div>
                                </div>

                                <div class="bg-base-200/40 hover:bg-white transition-all duration-300 p-6 rounded-[2rem] border border-transparent hover:border-primary/20 hover:shadow-xl group">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                        <div>
                                            <span class="text-[9px] font-black text-primary uppercase tracking-widest italic">#{{ $ticket->ticket_number ?? 'TK-'.$ticket->id }}</span>
                                            <h4 class="font-black text-sm uppercase tracking-tight group-hover:text-primary transition-colors">{{ $ticket->tindakan ?? 'Masalah Teknis' }}</h4>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-[9px] font-black opacity-30 uppercase tracking-widest">{{ $ticket->created_at->format('d F Y') }}</span>
                                            <span class="badge {{ $ticket->status == 'closed' ? 'badge-success' : 'badge-warning' }} badge-xs font-black uppercase text-[7px] py-2">{{ $ticket->status }}</span>
                                        </div>
                                    </div>
                                    <p class="text-xs font-bold opacity-60 leading-relaxed mb-4 italic">
                                        "{{ Str::limit($ticket->problem_detail ?? 'Tidak ada deskripsi detail.', 150) }}"
                                    </p>
                                    <div class="flex items-center gap-3 pt-4 border-t border-base-content/5">
                                        <div class="avatar">
                                            <div class="w-6 h-6 rounded-full opacity-50">
                                                <img src="https://ui-avatars.com/api/?name={{ $ticket->user->name ?? 'U' }}&background=random" />
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-black uppercase tracking-widest opacity-40">Dilaporkan Oleh: {{ $ticket->user->name ?? 'System' }}</span>
                                    </div>

                                    <div class="mt-4 text-right">
                                        <a href="{{ route('tickets.details', $ticket->id) }}" wire:navigate class="btn btn-primary btn-sm rounded-xl font-black uppercase text-[9px] tracking-widest opacity-50 hover:opacity-100">
                                            Lihat Detail
                                        </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-20 opacity-20">
                        <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-black italic uppercase tracking-widest text-xs text-center leading-relaxed">Aset ini belum pernah<br>dilaporkan bermasalah</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
