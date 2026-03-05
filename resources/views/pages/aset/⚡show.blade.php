<?php

use Livewire\Component;
use App\Models\Aset;
use App\Models\Ticket;
use App\Models\Departement;
use App\Models\AsetMaintenances;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

 new class extends Component {
    // Gunakan satu nama variabel yang konsisten
    public Aset $aset;
    public $title, $type, $maintenance_date, $cost, $description;

    public function mount($id)
    {
        // Load semua relasi yang dibutuhkan termasuk maintenances.user
        $this->aset = Aset::with(['tickets.user', 'departement', 'maintenances.user'])->findOrFail($id);
    }

    public function saveMaintenance()
    {
        $this->validate([
            'title' => 'required|min:3',
            'type' => 'required',
            'maintenance_date' => 'required|date',
            'description' => 'required',
        ]);

        AsetMaintenances::create([
            'aset_id' => $this->aset->id,
            'user_id' => auth()->id(),
            'title' => $this->title,
            'type' => $this->type,
            'maintenance_date' => $this->maintenance_date,
            'cost' => $this->cost ?? 0,
            'description' => $this->description,
        ]);

        $this->reset(['title', 'type', 'maintenance_date', 'cost', 'description']);

        // Refresh data aset agar history langsung muncul
        $this->aset->load('maintenances.user');

        $this->dispatch('close-modal');
        session()->flash('success', 'History pemeliharaan berhasil ditambahkan!');
    }

    public function render()
    {
        return view('pages.aset.⚡show')->layout('layouts.app');
    }
}; ?>
<div class="max-w-7xl mx-auto p-4 lg:p-10">
    <div class="flex items-center justify-between mb-10">
        <a href="{{ route('aset.index') }}" wire:navigate class="btn btn-ghost rounded-2xl gap-3 font-black uppercase text-[10px] tracking-widest opacity-50 hover:opacity-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <div class="flex gap-3">
            <a href="{{ route('aset.edit', $aset->id) }}" wire:navigate class="btn btn-outline border-2 rounded-2xl font-black text-[10px] uppercase italic px-8">
                Edit Unit
            </a>
            <button onclick="maintenance_modal.showModal()" class="btn btn-warning rounded-2xl font-black text-[10px] uppercase italic px-8 shadow-xl shadow-warning/20">
                Add Maintenance
            </button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-8">

        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="card bg-base-100 shadow-2xl rounded-[3rem] overflow-hidden border border-base-content/5">
                <div class="aspect-square relative group bg-base-200">
                    @if($aset->foto)
                        <img src="{{ asset('storage/' . $aset->foto) }}" class="w-full h-full object-cover p-3 rounded-[2.5rem]" alt="{{ $aset->nama_aset }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-base-content/10">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <div class="absolute bottom-6 right-6">
                        <div class="bg-white p-3 rounded-2xl shadow-2xl border border-base-content/5">
                            {!! QrCode::size(60)->generate($aset->qr_code ?? $aset->nomor_serial) !!}
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="badge badge-primary font-black italic uppercase text-[8px] py-3">{{ $aset->category->name ?? 'Uncategorized' }}</span>
                        <span class="badge badge-outline font-black italic uppercase text-[8px] py-3 border-2">{{ $aset->kondisi_aset }}</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tighter uppercase italic leading-none mb-1">{{ $aset->nama_aset }}</h1>
                    <p class="text-primary font-bold text-xs tracking-[0.2em] mb-6">{{ $aset->nomor_serial }}</p>

                    <div class="space-y-4 border-t border-base-content/5 pt-6">
                        @php
                            $details = [
                                'Lokasi' => $aset->lokasi_aset,
                                'Departemen' => $aset->departement->name ?? 'N/A',
                                'Tgl Perolehan' => \Carbon\Carbon::parse($aset->tgl_pembelian)->format('d M Y'),
                                'Nilai Aset' => 'Rp ' . number_format($aset->nilai_perolehan, 0, ',', '.')
                            ];
                        @endphp
                        @foreach($details as $label => $value)
                            <div class="flex justify-between items-center text-[10px] uppercase tracking-widest">
                                <span class="font-black opacity-30">{{ $label }}</span>
                                <span class="font-bold {{ $label == 'Nilai Aset' ? 'text-primary' : '' }}">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card bg-base-200/50 rounded-[2.5rem] p-8">
                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40 mb-4 text-center">Catatan</h3>
                <p class="text-xs font-bold leading-relaxed opacity-60 italic text-center italic">
                    "{{ $aset->keterangan ?? 'Tidak ada catatan tambahan.' }}"
                </p>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 space-y-8">

            <div class="card bg-white shadow-xl rounded-[3rem] p-8 lg:p-10 border border-base-content/5">
                <div class="flex items-center justify-between mb-10">
                    <h3 class="text-2xl font-black uppercase italic tracking-tighter">Repair <span class="text-primary">History</span></h3>
                    <div class="badge badge-ghost font-black text-[9px] uppercase tracking-widest py-3">Total {{ $aset->tickets->count() }} Laporan</div>
                </div>

                @forelse($aset->tickets->sortByDesc('created_at') as $ticket)
                    <div class="relative pl-8 pb-10 last:pb-0 group">
                        @if(!$loop->last)
                            <div class="absolute left-[11px] top-10 bottom-0 w-0.5 bg-base-content/5 transition-colors group-hover:bg-primary/20"></div>
                        @endif
                        <div class="absolute left-0 top-1 w-6 h-6 rounded-lg bg-base-200 flex items-center justify-center z-10 group-hover:bg-primary transition-colors">
                            <div class="w-2 h-2 rounded-full {{ $ticket->status == 'closed' ? 'bg-success' : 'bg-warning animate-pulse' }}"></div>
                        </div>

                        <div class="bg-base-200/30 group-hover:bg-base-200/50 p-6 rounded-[2rem] transition-all">
                            <div class="flex flex-col md:flex-row justify-between gap-4 mb-4">
                                <div>
                                    <span class="text-[9px] font-black text-primary uppercase italic tracking-widest">#{{ $ticket->ticket_number }}</span>
                                    <h4 class="font-black text-sm uppercase">{{ $ticket->tindakan ?? 'Laporan Masalah' }}</h4>
                                </div>
                                <div class="md:text-right">
                                    <span class="block text-[9px] font-black opacity-30 uppercase">{{ $ticket->created_at->format('d M Y') }}</span>
                                    <span class="badge {{ $ticket->status == 'closed' ? 'badge-success' : 'badge-warning' }} badge-xs font-black text-[7px] py-2">{{ $ticket->status }}</span>
                                </div>
                            </div>
                            <p class="text-xs font-medium opacity-60 italic mb-4">"{{ Str::limit($ticket->problem_detail, 120) }}"</p>
                            <div class="flex items-center justify-between pt-4 border-t border-base-content/5">
                                <div class="flex items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ $ticket->user->name }}&background=random" class="w-5 h-5 rounded-full opacity-50">
                                    <span class="text-[9px] font-black uppercase opacity-40">Reporter: {{ $ticket->user->name }}</span>
                                </div>
                                <a href="{{ route('tickets.details', $ticket->id) }}" wire:navigate class="text-primary font-black text-[9px] uppercase tracking-widest hover:underline">View Ticket</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 opacity-20 italic font-bold">No tickets found.</div>
                @endforelse
            </div>

            <div class="card bg-base-100 shadow-xl rounded-[3rem] p-8 lg:p-10 border border-base-content/5">
                <h3 class="text-2xl font-black uppercase italic tracking-tighter mb-10">Asset <span class="text-warning">Lifecycle</span></h3>

                <div class="space-y-6">
                    @forelse($aset->maintenances->sortByDesc('maintenance_date') as $log)
                        <div class="flex gap-6 items-start">
                            <div class="flex-none text-center w-16">
                                <span class="block text-[10px] font-black uppercase opacity-40">{{ \Carbon\Carbon::parse($log->maintenance_date)->format('M') }}</span>
                                <span class="block text-2xl font-black leading-none">{{ \Carbon\Carbon::parse($log->maintenance_date)->format('d') }}</span>
                            </div>
                            <div class="flex-1 bg-warning/5 rounded-3xl p-5 border border-warning/10 relative overflow-hidden">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-black text-sm uppercase">{{ $log->title }}</h4>
                                    <span class="badge badge-warning badge-sm font-black text-[8px] uppercase">{{ $log->type }}</span>
                                </div>
                                <p class="text-xs font-bold opacity-60 mb-3">{{ $log->description }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black opacity-40 uppercase italic">By: {{ $log->user->name }}</span>
                                    @if($log->cost > 0)
                                        <span class="text-[10px] font-black text-success uppercase">Rp {{ number_format($log->cost, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-20 italic font-bold uppercase tracking-widest">No maintenance logs recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

   <dialog id="maintenance_modal" class="modal" wire:ignore.self>
    <div class="modal-box bg-base-100 rounded-[2.5rem] p-0 max-w-xl border border-base-content/10 overflow-hidden shadow-2xl">
        <div class="bg-warning p-8 pb-10">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-black text-3xl uppercase italic tracking-tighter text-warning-content leading-none">
                        Add <span class="opacity-50">Log</span>
                    </h3>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-warning-content/60 mt-2">
                        Internal Asset Maintenance
                    </p>
                </div>
                <button type="button" onclick="maintenance_modal.close()" class="btn btn-square btn-ghost btn-sm text-warning-content">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <form wire:submit.prevent="saveMaintenance" class="p-8 -mt-6 bg-base-100 rounded-t-[2.5rem] relative">
            <div class="space-y-5">
                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest">Judul Kegiatan</span>
                    </label>
                    <input type="text" wire:model="title"
                        class="input input-bordered bg-base-200/50 border-none rounded-2xl font-bold focus:ring-2 ring-warning w-full"
                        placeholder="Misal: Upgrade SSD 512GB">
                    @error('title') <span class="text-error text-[10px] font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest">Tipe Tindakan</span>
                        </label>
                        <select wire:model="type" class="select select-bordered bg-base-200/50 border-none rounded-2xl font-bold focus:ring-2 ring-warning">
                            <option value="">Pilih Tipe</option>
                            <option value="Repair">Repair</option>
                            <option value="Upgrade">Upgrade</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                        @error('type') <span class="text-error text-[10px] font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest">Tanggal</span>
                        </label>
                        <input type="date" wire:model="maintenance_date"
                            class="input input-bordered bg-base-200/50 border-none rounded-2xl font-bold focus:ring-2 ring-warning">
                        @error('maintenance_date') <span class="text-error text-[10px] font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest">Estimasi Biaya (IDR)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black opacity-30 text-xs">Rp</span>
                        <input type="number" wire:model="cost"
                            class="input input-bordered bg-base-200/50 border-none rounded-2xl font-bold focus:ring-2 ring-warning w-full pl-12"
                            placeholder="0">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-black text-[10px] uppercase opacity-40 tracking-widest">Deskripsi Pekerjaan</span>
                    </label>
                    <textarea wire:model="description"
                        class="textarea textarea-bordered bg-base-200/50 border-none rounded-2xl font-bold h-28 focus:ring-2 ring-warning p-4"
                        placeholder="Jelaskan detail apa yang dikerjakan teknisi..."></textarea>
                    @error('description') <span class="text-error text-[10px] font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="modal-action mt-10">
                <button type="submit" class="btn btn-warning btn-block rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-warning/20 h-14">
                    <span wire:loading.remove wire:target="saveMaintenance">Simpan Log Perbaikan</span>
                    <span wire:loading wire:target="saveMaintenance" class="loading loading-spinner"></span>
                </button>
            </div>
        </form>
    </div>
</dialog>
</div>

<script>
    window.addEventListener('close-modal', event => {
        maintenance_modal.close();
    });
</script>
