<?php
use Livewire\Component;
use App\Models\{Ticket, Aset, Tickethistory, Departement, User};
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    // Property lengkap sesuai kebutuhan database Anda
    public  $aset_id, $category, $tindakan, $kode_ppp, $deskripsi;
    public $resource_type,  $problem_detail, $emergency_action, $attachment;
    public $alloted_time = "24";
    public $custom_date;

   public function createTicket()
    {
        // 1. Validasi dengan pesan kustom (opsional)
        $this->validate([
            'aset_id'          => 'required|exists:aset,id',
            'category'         => 'required',
            'tindakan'         => 'required',
            'kode_ppp'         => 'required',
            'resource_type'    => 'required',
            'emergency_action' => 'required',
            'problem_detail'   => 'required',
            'attachment'       => 'nullable|image|max:5120', // Max 5MB
            'custom_date'      => 'required_if:alloted_time,custom',
        ]);

        try {
            // 2. Logika Deadline (SLA)
            $deadline = ($this->alloted_time === 'custom')
                ? \Carbon\Carbon::parse($this->custom_date)
                : now()->addHours((int)$this->alloted_time);

            // 3. Penomoran Tiket Otomatis Berdasarkan Kode PPP
            $lastTicket = Ticket::where('ticket_number', 'like', $this->kode_ppp . '-%')
                ->orderByRaw('CAST(SUBSTRING_INDEX(ticket_number, "-", -1) AS UNSIGNED) DESC')
                ->first();

            $nextSequence = $lastTicket
                ? str_pad(((int)explode('-', $lastTicket->ticket_number)[1]) + 1, 3, '0', STR_PAD_LEFT)
                : '001';

            $ticketNumber = "{$this->kode_ppp}-{$nextSequence}";

            // 4. Proses Simpan Attachment
            $path = null;
            if ($this->attachment) {
                $path = $this->attachment->store('tickets', 'public');
            }

            // 5. Simpan ke Database (Ticket Utama)
            $ticket = Ticket::create([
                'user_id'          => Auth::id(),
                'ticket_number'    => $ticketNumber,
                'kode_ppp'         => $this->kode_ppp,
                'category'         => $this->category,
                'deskripsi'        => $this->deskripsi,
                'tindakan'         => $this->tindakan,
                'resource_type'    => $this->resource_type,
                'problem_detail'   => $this->problem_detail,
                'emergency_action' => $this->emergency_action,
                'status'           => 'pending',
                'alloted_time'     => $deadline,
                'aset_id'          => $this->aset_id,
                'attachment'       => $path,
            ]);

            // 6. Notifikasi History ke Admin/Superadmin
            $admins = User::whereIn('role', ['admin', 'superadmin'])->get();

            foreach ($admins as $admin) {
                Tickethistory::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => Auth::id(),
                    'receiver_id' => $admin->id,
                    'status_to'   => 'pending',
                    'comment'     => "Tiket #{$ticketNumber} baru saja dibuat oleh " . Auth::user()->name,
                ]);
            }

            // 7. Reset Form & Dispatch Event
            $this->reset(['category', 'aset_id', 'tindakan', 'deskripsi', 'kode_ppp', 'problem_detail', 'attachment', 'custom_date', 'resource_type', 'emergency_action']);
            $this->alloted_time = "24";

            // Trigger Reset JS (TomSelect)
            $this->dispatch('reset-tomselect');

            // SweetAlert Sukses
            session()->flash('ticket_updated', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Tiket #' . $ticketNumber . ' telah dibuat.'
        ]);

             return redirect()->route('tickets.index');

        } catch (\Exception $e) {
            // Log error untuk pengecekan admin
            Log::error('Ticket Creation Error: ' . $e->getMessage());

            // SweetAlert Gagal (Tanpa dd() agar tidak macet)
            $this->dispatch('show-alert', [
                'icon'  => 'error',
                'title' => 'Gagal!',
                'text'  => 'Sistem bermasalah: ' . $e->getMessage(),
            ]);
        }
    }

    public function render() {
        return view('pages.tickets.⚡created', [
            'departements' => Departement::orderBy('name', 'asc')->get(),
            'aset' => Aset::orderBy('nama_aset', 'asc')->get(),
        ])->layout('layouts.app');
    }
};?>

    <div class="max-w-4xl mx-auto pb-20">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 px-4">
        <div class="flex items-center gap-5">
            <div class="p-4 bg-primary rounded-3xl text-white shadow-2xl shadow-primary/30 rotate-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <h1 class="text-4xl font-black text-base-content uppercase tracking-tighter leading-none">Maintenance <span class="text-primary">Ticket</span></h1>
                <p class="text-[10px] font-bold opacity-40 uppercase tracking-[0.3em] mt-2">Pusat Pelaporan Kerusakan Aset & Properti</p>
            </div>
        </div>
        <div class="hidden md:block text-right">
            <span class="badge badge-outline border-base-300 font-bold py-4 px-6 rounded-full opacity-50 uppercase text-[10px] tracking-widest">Formulir Elektronik v2.0</span>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-visible rounded-[2.5rem]">
        <form wire:submit.prevent="createTicket" class="card-body p-8 lg:p-12 gap-8" enctype="multipart/form-data">

            {{-- SECTION 1: IDENTITAS ASET --}}
            <section class="space-y-6">
                <div class="flex items-center gap-4 border-b border-base-200 pb-4">
                    <span class="w-10 h-10 rounded-2xl bg-primary text-primary-content flex items-center justify-center font-black text-sm shadow-lg shadow-primary/20">01</span>
                    <div>
                        <h2 class="font-black uppercase text-sm tracking-widest text-base-content">Identifikasi Aset Terkait</h2>
                        <p class="text-[10px] uppercase tracking-tighter opacity-50 font-bold">Informasi dasar aset dan lokasi kerusakan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="form-control w-full">
                       <legend class="fieldset-legend">Kode PPP</legend>
                        <select wire:model="kode_ppp" class="select select-bordered rounded-2xl font-bold focus:ring-2 ring-primary/20 bg-base-50 h-14 @error('kode_ppp') select-error @enderror">
                            <option value="">Pilih Kode PPP</option>
                            <option value="OT">Other Departement</option>
                            <option value="PI">Plastic Injection</option>
                            <option value="SH">Safety Injection</option>
                            <option value="FS">Finishing</option>
                        </select>
                        @error('kode_ppp') <span class="text-error text-[10px] font-bold mt-2 px-1 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control w-full" wire:ignore>
                         <legend class="fieldset-legend">Pilih aset</legend>
                        <select id="aset_id" class="w-full @error('aset_id') select-error @enderror">
                            <option value="">Cari Aset...</option>
                            @foreach($aset as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_aset }} ({{ $item->nomor_serial ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        @error('aset_id') <span class="text-error text-[10px] font-bold mt-2 px-1 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control w-full">
                       <legend class="fieldset-legend">Resource Type</legend>
                        <input type="text" wire:model="resource_type" class="input input-bordered rounded-2xl font-bold focus:ring-2 ring-primary/20 bg-base-50 h-14 @error('resource_type') input-error @enderror" placeholder="Misal: Elektronik / Mekanik" />
                        @error('resource_type') <span class="text-error text-[10px] font-bold mt-2 px-1 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control w-full">
                        <legend class="fieldset-legend">Deskripsi</legend>
                        <input type="text" wire:model="deskripsi" class="input input-bordered rounded-2xl font-bold focus:ring-2 ring-primary/20 bg-base-50 h-14 @error('deskripsi') input-error @enderror" placeholder="Input deskripsi singkat..." />
                        @error('deskripsi') <span class="text-error text-[10px] font-bold mt-2 px-1 uppercase">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            <div class="divider opacity-20"></div>

            {{-- SECTION 2: DETAIL MASALAH --}}
            <section class="space-y-8">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondary text-secondary-content flex items-center justify-center font-black text-sm shadow-lg shadow-secondary/20">02</span>
                    <div>
                        <h2 class="font-black uppercase text-sm tracking-widest text-base-content">Detail Masalah & Klasifikasi</h2>
                        <p class="text-[10px] uppercase tracking-tighter opacity-50 font-bold">Klasifikasi teknis dan urgensi perbaikan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest text-primary">Kategori & Tindakan</span></label>
                        <div class="join w-full shadow-sm rounded-2xl border border-base-200 overflow-hidden">
                            <select wire:model="category" class="select join-item flex-1 font-bold text-sm bg-base-50 focus:bg-white border-r border-base-200 @error('category') select-error @enderror">
                                <option value="">Kategori</option>
                                <option value="Mesin">Mesin</option>
                                <option value="Peralatan">Peralatan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <select wire:model="tindakan" class="select join-item flex-1 font-bold text-sm bg-base-50 focus:bg-white @error('tindakan') select-error @enderror">
                                <option value="">Tindakan</option>
                                <option value="pemeliharaan">Pemeliharaan</option>
                                <option value="pemeriksaan">Pemeriksaan</option>
                                <option value="perbaikan">Perbaikan</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1 mt-2">
                            @error('category') <span class="text-error text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                            @error('tindakan') <span class="text-error text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Batas Waktu (SLA)</span></label>
                        <div class="join w-full shadow-sm border border-base-200 rounded-2xl overflow-hidden">
                            <input type="radio" wire:model.live="alloted_time" value="24" class="join-item btn btn-md flex-1 bg-base-50 font-black text-[10px]" aria-label="24 JAM" />
                            <input type="radio" wire:model.live="alloted_time" value="48" class="join-item btn btn-md flex-1 bg-base-50 font-black text-[10px]" aria-label="48 JAM" />
                            <input type="radio" wire:model.live="alloted_time" value="custom" class="join-item btn btn-md flex-1 bg-base-50 font-black text-[10px]" aria-label="KALENDER" />
                        </div>
                        @error('alloted_time') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror

                        @if($alloted_time === 'custom')
                            <div class="mt-4 transition-all animate-in fade-in slide-in-from-top-2">
                                <input type="date" wire:model="custom_date" class="input input-bordered w-full rounded-2xl font-bold text-primary bg-primary/5 border-primary/20" />
                                @error('custom_date') <span class="text-error text-[10px] font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest text-error opacity-70">Detail Kerusakan</span></label>
                        <textarea wire:model="problem_detail" class="textarea textarea-bordered rounded-2xl min-h-[140px] font-medium p-5 focus:ring-2 ring-error/20 bg-base-50 @error('problem_detail') textarea-error @enderror" placeholder="Jelaskan kronologi dan gejala..."></textarea>
                        @error('problem_detail') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest text-warning opacity-70">Tindakan Darurat</span></label>
                        <textarea wire:model="emergency_action" class="textarea textarea-bordered rounded-2xl min-h-[140px] font-medium p-5 focus:ring-2 ring-warning/20 bg-base-50 @error('emergency_action') textarea-error @enderror" placeholder="Tindakan sementara yang dilakukan..."></textarea>
                        @error('emergency_action') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-bold uppercase text-[10px] tracking-widest opacity-60">Bukti Foto / Lampiran</span></label>
                    <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-base-300 rounded-[2rem] cursor-pointer bg-base-50 hover:bg-base-200 hover:border-primary/50 transition-all group overflow-hidden relative">
                        @if ($attachment)
                            <img src="{{ $attachment->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-20">
                        @endif
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10">
                            <svg class="w-8 h-8 mb-2 text-base-content/20 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-[10px] font-black text-base-content/40 uppercase tracking-widest group-hover:text-primary">Klik untuk unggah atau seret file</p>
                        </div>
                        <input type="file" wire:model="attachment" class="hidden" />
                    </label>

                    <div wire:loading wire:target="attachment" class="text-[10px] text-primary font-black mt-3 uppercase tracking-widest animate-pulse">
                        ⚡ Mengunggah Berkas ke Server...
                    </div>

                    @if ($attachment)
                        <div class="mt-4 p-4 bg-success/10 border border-success/20 rounded-3xl flex items-center gap-4 animate-in zoom-in-95">
                            <img src="{{ $attachment->temporaryUrl() }}" class="h-16 w-16 object-cover rounded-2xl shadow-lg border-2 border-white">
                            <div>
                                <p class="text-[10px] font-black text-success uppercase tracking-widest">Siap Dikirim!</p>
                                <p class="text-[9px] font-bold opacity-60 uppercase">File Berhasil Diverifikasi</p>
                            </div>
                        </div>
                    @endif
                    @error('attachment') <span class="text-error text-[10px] font-bold mt-2 uppercase">{{ $message }}</span> @enderror
                </div>
            </section>

            {{-- Submit Button --}}
            <div class="card-actions justify-center lg:justify-end mt-6">
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-lg px-12 rounded-3xl font-black tracking-[0.2em] shadow-2xl shadow-primary/40 uppercase text-xs group">
                    <span wire:loading.remove>🚀 Submit Ticket</span>
                    <span wire:loading>⚙️ Processing...</span>
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
