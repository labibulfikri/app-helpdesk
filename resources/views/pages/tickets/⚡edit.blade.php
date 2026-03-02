<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Ticket;
use App\Models\Aset;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

new class extends Component
{
    use WithFileUploads;

    public $ticket;
    public $aset_id, $kode_ppp, $resource_type, $deskripsi;
    public $category, $tindakan, $problem_detail, $emergency_action;
    public $alloted_time, $custom_date;
    public $attachment, $old_attachment;

    public function mount($id) {
        $this->ticket = Ticket::findOrFail($id);
        $this->syncData();
    }

    public function syncData()
    {
        // Pastikan casting ke string agar select/radio sinkron
        $this->aset_id          = (string) $this->ticket->aset_id;
        $this->kode_ppp         = $this->ticket->kode_ppp;
        $this->resource_type    = $this->ticket->resource_type;
        $this->deskripsi        = $this->ticket->deskripsi;
        $this->category         = $this->ticket->category;
        $this->tindakan         = $this->ticket->tindakan;
        $this->problem_detail   = $this->ticket->problem_detail;
        $this->emergency_action = $this->ticket->emergency_action;
        $this->old_attachment   = $this->ticket->attachment;

        if ($this->ticket->alloted_time) {
        $dbDate = Carbon::parse($this->ticket->alloted_time)->format('Y-m-d');
        $besok = now()->addDay()->format('Y-m-d');
        $lusa  = now()->addDays(2)->format('Y-m-d');

        if ($dbDate === $besok) {
            $this->alloted_time = '24';
        } elseif ($dbDate === $lusa) {
            $this->alloted_time = '48';
        } else {
            $this->alloted_time = 'custom';
            $this->custom_date  = $dbDate;
        }
    }
        // $sla = (string) $this->ticket->alloted_time;
        // if (in_array($sla, ['24', '48'])) {
        //     $this->alloted_time = $sla;
        // } else {
        //     $this->alloted_time = 'custom';
        //     $this->custom_date = $sla ? Carbon::parse($sla)->format('Y-m-d') : now()->format('Y-m-d');
        // }
    }

    public function updateTicket()
    {
        // Validasi
        $validated = $this->validate([
            'aset_id'          => 'required',
            'kode_ppp'         => 'required',
            'resource_type'    => 'required',
            'deskripsi'        => 'required',
            'category'         => 'required',
            'tindakan'         => 'required',
            'alloted_time'     => 'required',
            'custom_date'      => 'required_if:alloted_time,custom',
            'problem_detail'   => 'required|min:5',
            'emergency_action' => 'required',
            'attachment'       => 'nullable|image|max:5120',
        ]);

        try {
            $attachmentPath = $this->old_attachment;

            if ($this->attachment) {
                if ($this->old_attachment) {
                    Storage::disk('public')->delete($this->old_attachment);
                }
                $attachmentPath = $this->attachment->store('tickets', 'public');
            }

            // Hitung nilai alloted_time yang akan disimpan
            // $finalSla = ($this->alloted_time === 'custom') ? $this->custom_date : $this->alloted_time;
            $finalDate = null;

        if ($this->alloted_time === '24') {
            // Jika pilih 24 Jam, simpan tanggal BESOK
            $finalDate = now()->addDay()->format('Y-m-d');
        } elseif ($this->alloted_time === '48') {
            // Jika pilih 48 Jam, simpan tanggal LUSA
            $finalDate = now()->addDays(2)->format('Y-m-d');
        } elseif ($this->alloted_time === 'custom') {
            // Jika pilih Kalender, simpan tanggal yang dipilih user
            $finalDate = $this->custom_date;
        }

            // Eksekusi Update secara eksplisit
            $this->ticket->update([
                'aset_id'          => $this->aset_id,
                'kode_ppp'         => $this->kode_ppp,
                'resource_type'    => $this->resource_type,
                'deskripsi'        => $this->deskripsi,
                'category'         => $this->category,
                'tindakan'         => $this->tindakan,
                'alloted_time'     => $finalDate,
                'problem_detail'   => $this->problem_detail,
                'emergency_action' => $this->emergency_action,
                'attachment'       => $attachmentPath,
            ]);

            session()->flash('ticket_updated', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Tiket #' . $this->ticket->ticket_number . ' telah diperbarui.'
        ]);

        return redirect()->route('tickets.index');

        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        // Pastikan nama view tidak pakai emoji
        return view('pages.tickets.⚡edit', [
            'aset' => Aset::all()
        ])->layout('layouts.app');
    }
}; ?>

<div class="max-w-4xl mx-auto pb-20 px-4">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 mt-10">
        <div class="flex items-center gap-5">
            <div class="p-4 bg-primary rounded-3xl text-white shadow-2xl rotate-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <h1 class="text-4xl font-black text-base-content uppercase tracking-tighter leading-none">Edit <span class="text-primary">Ticket</span></h1>
                <p class="text-[10px] font-bold opacity-40 uppercase tracking-[0.3em] mt-2">Nomor Tiket: {{ $ticket->ticket_number }}</p>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-visible rounded-[2.5rem]">
        <form wire:submit.prevent="updateTicket" enctype="multipart/form-data" class="card-body p-8 lg:p-12 gap-8">

            {{-- Identifikasi Aset --}}
            <section class="space-y-6">
                <div class="flex items-center gap-4 border-b border-base-200 pb-4">
                    <span class="w-10 h-10 rounded-2xl bg-primary text-primary-content flex items-center justify-center font-black text-sm">01</span>
                    <h2 class="font-black uppercase text-sm tracking-widest">Identifikasi Aset</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-control">
                        <label class="label font-bold text-[10px] uppercase opacity-60">Kode PPP</label>
                        <select wire:model="kode_ppp" class="select select-bordered rounded-2xl font-bold bg-base-50 h-14">
                            <option value="OT">Other Departement</option>
                            <option value="PI">Plastic Injection</option>
                            <option value="SH">Safety Injection</option>
                            <option value="FS">Finishing</option>
                        </select>
                        @error('kode_ppp') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control" wire:ignore>
                        <label class="label font-bold text-[10px] uppercase opacity-60">Pilih Aset</label>
                        <select id="aset_id_select" class="w-full">
                            <option value="">Cari Aset...</option>
                            @foreach($aset as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_aset }} ({{ $item->nomor_serial ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        @error('aset_id') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label font-bold text-[10px] uppercase opacity-60">Resource Type</label>
                        <input type="text" wire:model="resource_type" class="input input-bordered rounded-2xl font-bold bg-base-50 h-14" />
                        @error('resource_type') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label font-bold text-[10px] uppercase opacity-60">Deskripsi Singkat</label>
                        <input type="text" wire:model="deskripsi" class="input input-bordered rounded-2xl font-bold bg-base-50 h-14" />
                        @error('deskripsi') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            {{-- Detail & SLA --}}
            <section class="space-y-8">
                <div class="flex items-center gap-4 border-b border-base-200 pb-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondary text-secondary-content flex items-center justify-center font-black text-sm">02</span>
                    <h2 class="font-black uppercase text-sm tracking-widest">Detail & SLA</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="form-control">
                        <label class="label font-bold uppercase text-[10px] text-primary">Kategori & Tindakan</label>
                        <div class="join w-full shadow-sm rounded-2xl border border-base-200 overflow-hidden">
                            <select wire:model="category" class="select join-item flex-1 font-bold bg-base-50">
                                <option value="Mesin">Mesin</option>
                                <option value="Peralatan">Peralatan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <select wire:model="tindakan" class="select join-item flex-1 font-bold bg-base-50 border-l">
                                <option value="pemeliharaan">Pemeliharaan</option>
                                <option value="pemeriksaan">Pemeriksaan</option>
                                <option value="perbaikan">Perbaikan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label font-bold uppercase text-[10px] opacity-60">Batas Waktu (SLA)</label>
                        <div class="join w-full shadow-sm border border-base-200 rounded-2xl overflow-hidden">
                            <input type="radio" wire:model.live="alloted_time" value="24" class="join-item btn btn-md flex-1 bg-base-50 font-black text-[10px]" aria-label="24 JAM" />
                            <input type="radio" wire:model.live="alloted_time" value="48" class="join-item btn btn-md flex-1 bg-base-50 font-black text-[10px]" aria-label="48 JAM" />
                            <input type="radio" wire:model.live="alloted_time" value="custom" class="join-item btn btn-md flex-1 bg-base-50 font-black text-[10px]" aria-label="KALENDER" />
                        </div>
                        @if($alloted_time === 'custom')
                            <div class="mt-4">
                                <input type="date" wire:model="custom_date" class="input input-bordered w-full rounded-2xl font-bold bg-primary/5 border-primary/20" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="form-control">
                        <label class="label font-bold uppercase text-[10px] text-error">Deskripsi Kerusakan</label>
                        <textarea wire:model="problem_detail" class="textarea textarea-bordered rounded-2xl min-h-[140px] bg-base-50"></textarea>
                    </div>
                    <div class="form-control">
                        <label class="label font-bold uppercase text-[10px] text-warning">Tindakan Darurat</label>
                        <textarea wire:model="emergency_action" class="textarea textarea-bordered rounded-2xl min-h-[140px] bg-base-50"></textarea>
                    </div>
                </div>

                <div class="form-control w-full">
                    <label class="label font-bold uppercase text-[10px] opacity-60">Foto Lampiran</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-base-300 rounded-[2rem] h-32 bg-base-50 cursor-pointer relative overflow-hidden">
                            @if($attachment)
                                <img src="{{ $attachment->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-40">
                            @endif
                            <span class="text-[10px] font-black opacity-40 uppercase z-10">Ganti Foto</span>
                            <input type="file" wire:model="attachment" class="hidden" />
                        </label>
                        <div class="h-32 rounded-[2rem] border overflow-hidden bg-base-200">
                            @if($old_attachment)
                                <img src="{{ asset('storage/'.$old_attachment) }}" class="w-full h-full object-cover opacity-50">
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <div class="card-actions justify-end mt-6 gap-4">
                <a href="{{ route('tickets.index') }}" class="btn btn-ghost btn-lg rounded-3xl font-black text-xs uppercase">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg px-12 rounded-3xl font-black shadow-2xl uppercase text-xs">
                    🚀 Update Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        let ts;
        const initTS = () => {
            const el = document.getElementById('aset_id_select');
            if (el) {
                if (el.tomselect) el.tomselect.destroy();
                ts = new TomSelect(el, {
                    create: false,
                    dropdownParent: 'body',
                    onChange: (val) => {
                        @this.set('aset_id', val);
                    }
                });
                // Set initial value
                ts.setValue(@js($aset_id));
            }
        };

        initTS();
        document.addEventListener('livewire:navigated', initTS);

        Livewire.on('show-alert', (e) => {
            const data = Array.isArray(e) ? e[0] : e;
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon,
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary px-10 rounded-xl' }
            });
        });
    });
</script>
