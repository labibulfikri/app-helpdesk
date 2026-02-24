<?php
use Livewire\Component;
use App\Models\{Ticket, Aset, Tickethistory, Departement};
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    // Property lengkap sesuai kebutuhan database Anda
    public $target_departement_id, $aset_id, $category, $tindakan;
    public $resource_type,  $problem_detail, $emergency_action, $attachment;
    public $alloted_time = "24";
    public $custom_date;

   public function createTicket() {
    // 1. Validasi
    $validated = $this->validate([
        'target_departement_id' => 'required|exists:departements,id',
        'aset_id'               => 'required|exists:aset,id',
        'category'              => 'required',
        'tindakan'              => 'required',
        'resource_type'         => 'required',
        'emergency_action'         => 'required',
        'problem_detail'        => 'required',
        'attachment'            => 'nullable|image|max:5120', // Max 5MB
        'custom_date'           => 'required_if:alloted_time,custom',
    ]);

    try {
        $dept = Departement::findOrFail($this->target_departement_id);

        // 2. Logika Deadline
        $deadline = ($this->alloted_time === 'custom')
            ? \Carbon\Carbon::parse($this->custom_date)
            : now()->addHours((int)$this->alloted_time);

        // 3. Penomoran Tiket
        $lastTicket = Ticket::where('ticket_number', 'like', $dept->code . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(ticket_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $nextSequence = $lastTicket
            ? str_pad(((int)explode('-', $lastTicket->ticket_number)[1]) + 1, 3, '0', STR_PAD_LEFT)
            : '001';
        $ticketNumber = "{$dept->code}-{$nextSequence}";

        // 4. Proses Simpan Attachment (Perbaikan utama di sini)
        $path = null;
        if ($this->attachment) {
            // Gunakan store() secara explisit ke disk 'public'
            $path = $this->attachment->store('tickets', 'public');
        }

        // 5. Simpan ke Database
        $ticket = Ticket::create([
            'user_id'               => Auth::id(),
            'ticket_number'         => $ticketNumber,
            'target_departement_id' => $this->target_departement_id,
            'category'              => $this->category,
            'tindakan'              => $this->tindakan,
            'resource_type'         => $this->resource_type,
            'problem_detail'        => $this->problem_detail,
            'emergency_action'      => $this->emergency_action,
            'status'                => 'pending',
            'alloted_time'          => $deadline,
            'aset_id'               => $this->aset_id,
            'attachment'            => $path, // Menyimpan path string hasil upload
        ]);


        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
        Tickethistory::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'receiver_id' => $admin->id,
            'status_to' => 'pending',
            'comment'   => 'Tiket Maintenance berhasil dibuat.',
        ]);
        }
        // 6. Reset & Notifikasi
        $this->reset(['category', 'aset_id', 'tindakan', 'problem_detail', 'target_departement_id', 'attachment', 'custom_date', 'resource_type', 'emergency_action']);
        $this->alloted_time = "24";

        $this->dispatch('reset-tomselect');
        // Bagian akhir fungsi createTicket()
$this->dispatch('show-alert', [
    'title' => 'Berhasil!',
    'text'  => "Tiket #{$ticketNumber} berhasil dikirim.",
    'icon'  => 'success'
]);
        // $this->dispatch('alert', title: 'Berhasil!', text: "Tiket #{$ticketNumber} berhasil dikirim.", icon: 'success');

    } catch (\Exception $e) {

        dd($e->getMessage());
        // Gunakan \Log untuk melihat error sebenarnya di storage/logs/laravel.log
        \Log::error('Ticket Creation Error: ' . $e->getMessage());

        $this->dispatch('alert', title: 'Gagal!', text: 'Terjadi kesalahan sistem: ' . $e->getMessage(), icon: 'error');
    }
}

    public function render() {
        return view('pages.tickets.⚡created', [
            'departements' => Departement::orderBy('name', 'asc')->get(),
            'aset' => Aset::orderBy('nama_aset', 'asc')->get(),
        ])->layout('layouts.app');
    }
};?>

    <div class="max-w-4xl mx-auto">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-primary rounded-3xl text-white shadow-2xl shadow-primary/30 rotate-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-black text-base-content uppercase tracking-tighter leading-none">Maintenance <span class="text-primary text-outline">Ticket</span></h1>
                    <p class="text-[10px] font-bold opacity-40 uppercase tracking-[0.3em] mt-2">Pusat Pelaporan Kerusakan Aset & Properti</p>
                </div>
            </div>
            <div class="hidden md:block">
                <span class="badge badge-outline border-base-300 font-bold py-4 px-6 rounded-full opacity-50 uppercase text-[10px] tracking-widest">Formulir Elektronik v2.0</span>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200 overflow-visible rounded-[2.5rem]">
            <form wire:submit.prevent="createTicket" class="card-body p-8 lg:p-12 gap-10" enctype="multipart/form-data">

                {{-- SECTION 1: IDENTITAS ASET --}}
                <section class="space-y-6">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-black text-xs">01</span>
                        <h2 class="font-black uppercase text-sm tracking-widest text-base-content/80">Identifikasi Aset Terkait</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Departemen Tujuan</span></label>
                            <select wire:model="target_departement_id" class="select select-bordered rounded-2xl font-bold focus:ring-2 ring-primary bg-base-50 h-14 transition-all">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departements as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('target_departement_id') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control w-full" wire:ignore>
                            <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Pilih Aset</span></label>
                            <select id="aset_id" class="w-full">
                                <option value="">Cari Aset...</option>
                                @foreach($aset as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_aset }} ({{ $item->nomor_serial ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            @error('aset_id') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                        </div>

                    <div class="form-control max-w-md">
                        <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Resource Type</span></label>
                        <input type="text" wire:model="resource_type" class="input input-bordered rounded-2xl font-bold bg-base-50 h-14" placeholder="Misal: Elektronik / Mekanik" />
                        @error('resource_type') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                    </div>

                    </div>
                </section>

                <div class="divider opacity-50"></div>

                {{-- SECTION 2: DETAIL MASALAH --}}
                <section class="space-y-6">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="w-8 h-8 rounded-full bg-secondary/10 text-secondary flex items-center justify-center font-black text-xs">02</span>
                        <h2 class="font-black uppercase text-sm tracking-widest text-base-content/80">Detail Masalah & Klasifikasi</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60 text-primary">Kategori & Tindakan</span></label>
                            <div class="join join-vertical lg:join-horizontal w-full shadow-sm rounded-2xl overflow-hidden border border-base-200">
                                <select wire:model="category" class="select select-ghost join-item flex-1 font-bold text-sm bg-base-50 border-r border-base-200">
                                    <option value="Mesin">Mesin</option>
                                    <option value="Peralatan">Peralatan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                @error('category') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                                <select wire:model="tindakan" class="select select-ghost join-item flex-1 font-bold text-sm bg-base-50">
                                    <option value="pemeliharaan">Pemeliharaan </option>
                                    <option value="pemeriksaan">Pemeriksaan </option>
                                    <option value="perbaikan">Perbaikan</option>
                                </select>
                                @error('tindakan')<span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Batas Waktu (SLA)</span></label>
                            <div class="join w-full shadow-sm rounded-2xl overflow-hidden border border-base-200">
                                <input type="radio" wire:model.live="alloted_time" value="24" class="join-item btn btn-md flex-1 bg-base-50 border-none font-bold text-xs" aria-label="24 JAM" />
                                <input type="radio" wire:model.live="alloted_time" value="48" class="join-item btn btn-md flex-1 bg-base-50 border-none font-bold text-xs" aria-label="48 JAM" />
                                <input type="radio" wire:model.live="alloted_time" value="custom" class="join-item btn btn-md flex-1 bg-base-50 border-none font-bold text-xs" aria-label="KALENDER" />
                                    @error('alloted_time') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <div class="form-control justify-end">
                            @if($alloted_time === 'custom')
                            <br>
                                <input type="date" wire:model="custom_date" class="input input-bordered rounded-2xl font-bold text-primary h-12 bg-primary/5 border-primary/20 animate-bounce-short" />
                                @error('custom_date') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60 text-error">Detail Kerusakan</span></label>
                            <textarea wire:model="problem_detail" class="textarea textarea-bordered rounded-2xl min-h-[150px] font-medium p-5 focus:ring-2 ring-error/20 bg-base-50" placeholder="Jelaskan secara teknis kronologi dan gejala kerusakan..."></textarea>
                            @error('problem_detail') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60 text-warning">Tindakan Darurat</span></label>
                            <textarea wire:model="emergency_action" class="textarea textarea-bordered rounded-2xl min-h-[150px] font-medium p-5 focus:ring-2 ring-warning/20 bg-base-50" placeholder="Apa tindakan sementara yang sudah dilakukan di lokasi?"></textarea>
                            @error('emergency_action') <span class="text-error text-[10px] font-bold mt-1 uppercase tracking-tighter">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Bukti Foto / Lampiran</span></label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-base-300 rounded-[2rem] cursor-pointer bg-base-50 hover:bg-base-200 hover:border-primary/50 transition-all group">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-base-content/20 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="text-[10px] font-bold text-base-content/40 uppercase tracking-widest group-hover:text-primary">Klik untuk unggah atau seret file</p>
                                </div>
                                <input type="file" wire:model="attachment" class="hidden" />
                            </label>
                        </div>

                        <div wire:loading wire:target="attachment" class="text-[10px] text-primary font-black mt-3 uppercase tracking-widest animate-pulse">
                            ⚡ Mengunggah Berkas ke Server...
                        </div>

                        @if ($attachment)
                            <div class="mt-4 p-4 bg-success/10 border border-success/20 rounded-3xl flex items-center gap-4">
                                <img src="{{ $attachment->temporaryUrl() }}" class="h-16 w-16 object-cover rounded-2xl shadow-lg border-2 border-white">
                                <div>
                                    <p class="text-[10px] font-black text-success uppercase tracking-widest">Siap Dikirim!</p>
                                    <p class="text-[9px] font-bold opacity-60 uppercase">File Berhasil Diverifikasi</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <div class="card-actions justify-center lg:justify-end mt-10">
                    <button type="submit" class="btn btn-primary btn-lg px-12 rounded-3xl font-black tracking-[0.2em] shadow-2xl shadow-primary/40 uppercase text-xs">
                        🚀 Submit Ticket
                    </button>
                </div>
            </form>
        </div>

    </div>
    <script>
        document.addEventListener("livewire:navigated", function() {
            const component = @this;
            const el = document.getElementById('aset_id');

            if(el) {
                const ts = new TomSelect(el, {
                    onChange: (val) => { component.set('aset_id', val); }
                });
                window.addEventListener('reset-tomselect', () => { ts.clear(); });
            }

            window.addEventListener('swal', event => {
                Swal.fire({
                    title: event.detail[0].title,
                    text: event.detail[0].text,
                    icon: event.detail[0].icon,
                    customClass: { confirmButton: 'btn btn-primary px-8 rounded-lg' },
                    buttonsStyling: false
                });
            });
        });
    </script>

     <script>
    document.addEventListener('livewire:init', () => {
        let tsInstance;

        // 1. Inisialisasi TomSelect
        const initTomSelect = () => {
            const el = document.getElementById('aset_id');
            if (el) {
                // Hancurkan jika sudah ada untuk menghindari duplikasi saat navigasi
                if (el.tomselect) el.tomselect.destroy();

                tsInstance = new TomSelect(el, {
                    onChange: (val) => {
                        // @this adalah cara Livewire mengakses property komponen
                        @this.set('aset_id', val);
                    }
                });
            }
        };

        initTomSelect();

        // 2. Handle SweetAlert (Menggunakan event 'show-alert')
        Livewire.on('show-alert', (eventData) => {
            // Data di Livewire 3 biasanya dibungkus dalam array atau langsung objek
            // Kita ambil indeks pertama jika itu array
            const data = Array.isArray(eventData) ? eventData[0] : eventData;

            Swal.fire({
                title: data.title || 'Notifikasi',
                text: data.text || '',
                icon: data.icon || 'info',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary px-10 rounded-xl font-bold uppercase text-xs tracking-widest'
                }
            });
        });

        // 3. Handle Reset TomSelect
        Livewire.on('reset-tomselect', () => {
            if (tsInstance) tsInstance.clear();
        });

        // 4. Re-inisialisasi saat navigasi (jika menggunakan wire:navigate)
        document.addEventListener('livewire:navigated', () => {
            initTomSelect();
        });
    });
</script>
    {{-- <style>
        .ts-control { border-radius: 0.5rem !important; padding: 0.7rem !important; }
        .ts-wrapper.focus .ts-control { border-color: #570df8 !important; box-shadow: none !important; }
    </style> --}}
