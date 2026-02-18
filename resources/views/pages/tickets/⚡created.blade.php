<?php

use Livewire\Component;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Aset;
use App\Models\Tickethistory;
use App\Models\Departement; // Sesuaikan jika nama model Anda 'Department'
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    // Properti harus dideklarasikan agar bisa ditangkap wire:model
    public $target_departement_id;
    public $aset_id;
    public $category;
    public $tindakan;
    public $resource_type;
    public $unit_number;
    public $problem_detail;
    public $emergency_action;
    public $alloted_time = "24"; // Set default agar tidak null
    public $attachment;

    public function createTicket()
    {
        // 1. Validasi ketat sesuai nama variabel
        $this->validate([
            'target_departement_id'     => 'required',
            'category'       => 'required',
            'aset_id'        => 'required',
            'tindakan'       => 'required',
            'problem_detail' => 'required|min:10',
            'attachment'     => 'nullable|image|max:2048',
        ]);

        try {
            $user = Auth::user();

            // 2. Logic Nomor Tiket berdasarkan departemen yang DIPILIH di form
            $deptCode = Departement::find($this->target_departement_id)->code;
            $today = now()->format('Ymd');

            $lastTicket = Ticket::where('ticket_number', 'like', $deptCode . '-' . $today . '-%')
                ->latest()
                ->first();

            if ($lastTicket) {
                $parts = explode('-', $lastTicket->ticket_number);
                $lastNum = (int)end($parts);
                $nextSequence = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $nextSequence = '001';
            }

            $ticketNumber = "{$deptCode}-{$today}-{$nextSequence}";

            // 3. Simpan File
            $path = $this->attachment ? $this->attachment->store('tickets', 'public') : null;

            // 4. Cari ID Departemen berdasarkan kode yang dipilih
            $targetDept = Departement::where('code', $deptCode)->first();

            // 5. Eksekusi Create

            $allotedTime = now()->addHours(24);
            $ticket =Ticket::create([
                'user_id'              => $user->id,
                'ticket_number'        => $ticketNumber,
                // 'target_departement_id' => $targetDept ? $targetDept->id : null,
                'target_departement_id' => $this->target_departement_id,
                'category'             => $this->category,
                'tindakan'             => $this->tindakan,
                'resource_type'        => $this->resource_type,
                'unit_number'          => $this->unit_number,
                'problem_detail'          => $this->problem_detail,
                'emergency_action'     => $this->emergency_action,
                'alloted_time'         => $allotedTime,
                'attachment'           => $path,
                'status'               => 'pending',
                'aset_id'              => $this->aset_id,
            ]);

            // 2. Simpan ke Tabel Histori
    if ($ticket) {
        Tickethistory::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'status_from' => null,       // Belum ada status sebelumnya
            'status_to'   => 'pending',  // Status pertama saat dibuat
            'comment'     => 'Tiket baru berhasil dibuat oleh user.', // Pesan progres awal
        ]);
    }

            // 6. Reset & Notifikasi
            $this->reset(['category', 'aset_id', 'tindakan', 'resource_type', 'unit_number', 'problem_detail', 'emergency_action', 'attachment', 'target_departement_id']);
            session()->flash('success', "Tiket #{$ticketNumber} Berhasil terkirim ke sistem.");

        } catch (\Exception $e) {
            session()->flash('error', "Gagal menyimpan: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('pages.tickets.⚡created', [
            'departements' => Departement::all(),
            'aset' => Aset::all(),
        ])->layout('layouts.app');
    }
};
?>

<div class="max-w-5xl mx-auto p-4 lg:p-10">
    @if (session()->has('success'))
        <div class="alert alert-success shadow-lg mb-6 border-none text-white font-bold text-xs uppercase tracking-widest">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error shadow-lg mb-6 border-none text-white font-bold text-xs uppercase tracking-widest">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 border border-base-300 shadow-xl overflow-hidden">
        <div class="bg-neutral text-neutral-content px-8 py-4">
            <h2 class="text-[11px] font-black uppercase tracking-[0.2em] flex items-center gap-2">
                <span class="badge badge-primary badge-xs"></span> I. Informasi Pengguna & Identifikasi Aset
            </h2>
        </div>

        <form wire:submit.prevent="createTicket" enctype="multipart/form-data" class="card-body p-8 lg:p-10 gap-y-8">

            <div class="form-control w-full">
                <label class="label pt-0">
                    <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">1. Departemen Tujuan (Target)</span>
                </label>
                <select wire:model="target_departement_id" class="select select-bordered w-full focus:select-primary font-bold">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departements as $dept)
                        {{-- <option value="{{ $dept->code }}">{{ $dept->name }}</option> --}}
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('target_departement_id') <span class="label-text-alt text-error font-bold mt-1 uppercase text-[10px] italic">{{ $message }}</span> @enderror
            </div>


            <div class="form-control w-full">
                <label class="label pt-0">
                    <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">2. Aset (Target)</span>
                </label>
                <select wire:model="aset_id" class="select select-bordered w-full focus:select-primary font-bold">
                    <option value="">-- Pilih Aset --</option>
                    @foreach($aset as $aset)
                        <option value="{{ $aset->id }}">{{ $aset->nama_aset }}</option>
                    @endforeach

                </select>
                @error('aset_id') <span class="label-text-alt text-error font-bold mt-1 uppercase text-[10px] italic">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">2. Jenis Aset</span>
                    </label>
                    <select wire:model="category" class="select select-bordered w-full focus:select-primary font-black italic text-primary">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Mesin">Mesin</option>
                        <option value="Peralatan">Peralatan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    @error('category') <span class="label-text-alt text-error font-bold mt-1 uppercase text-[10px] italic">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">3. Kategori Tindakan</span>
                    </label>
                    <select wire:model="tindakan" class="select select-bordered w-full focus:select-primary font-bold">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="pemeliharaan">Pemeliharaan</option>
                        <option value="pemeriksaan">Pemeriksaan</option>
                        <option value="perbaikan">Perbaikan</option>
                    </select>
                    @error('tindakan') <span class="label-text-alt text-error font-bold mt-1 uppercase text-[10px] italic">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">4. Resource / Tipe</span>
                    </label>
                    <input type="text" wire:model="resource_type" placeholder="Contoh: Kompresor Angin" class="input input-bordered focus:input-primary w-full font-semibold" />
                </div>
                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">5. Deskripsi / No. Unit</span>
                    </label>
                    <input type="text" wire:model="unit_number" placeholder="Contoh: SBE-UNIT-01" class="input input-bordered focus:input-primary w-full font-semibold" />
                </div>
            </div>

            <div class="form-control w-full flex flex-col gap-2">
                <label class="label pt-0">
                    <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60 border-t border-base-200 pt-6 w-full">6. Detail Kerusakan (Problem Detail)</span>
                </label>
                <textarea wire:model="problem_detail" class="textarea textarea-bordered w-full h-40 focus:textarea-primary leading-relaxed p-4 font-medium" placeholder="Jelaskan kendala teknis secara detail..."></textarea>
                @error('problem_detail') <span class="label-text-alt text-error font-bold mt-1 uppercase text-[10px] italic">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-7 form-control">
                    <label class="label pt-0">
                        <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">7. Tindakan Darurat (Emergency Action)</span>
                    </label>
                    <textarea wire:model="emergency_action" class="textarea textarea-bordered focus:textarea-primary h-28 italic font-semibold text-sm" placeholder="Langkah sementara yang diambil..."></textarea>
                </div>

                <div class="lg:col-span-5 flex flex-col justify-start">
                    <label class="label pt-0">
                        <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">8. Alokasi Waktu Maksimal</span>
                    </label>
                    <div class="bg-base-200 border border-base-300 rounded-xl p-6 space-y-4">
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <input type="radio" wire:model="alloted_time" value="24" class="radio radio-primary radio-sm shadow-sm" />
                            <span class="label-text font-black text-xs uppercase tracking-tight opacity-70 group-hover:opacity-100 transition-opacity">Prioritas Utama (Maks. 24 Jam)</span>
                        </label>
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <input type="radio" wire:model="alloted_time" value="48" class="radio radio-primary radio-sm shadow-sm" />
                            <span class="label-text font-black text-xs uppercase tracking-tight opacity-70 group-hover:opacity-100 transition-opacity">Normal (Maks. 48 Jam)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-control w-full max-w-md">
                <label class="label pt-0">
                    <span class="label-text font-extrabold uppercase text-[10px] tracking-widest opacity-60">9. Lampiran Foto Kondisi Aset</span>
                </label>
                <input type="file" wire:model="attachment" class="file-input file-input-bordered file-input-primary w-full shadow-sm" />
                <div wire:loading wire:target="attachment" class="mt-3 flex items-center gap-3 text-primary">
                    <span class="loading loading-spinner loading-xs"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest animate-pulse">Sinkronisasi Data...</span>
                </div>
                @error('attachment') <span class="label-text-alt text-error font-bold mt-1 uppercase text-[10px] italic">{{ $message }}</span> @enderror
            </div>

            <div class="card-actions justify-end border-t border-base-300 pt-8 mt-4 gap-4">
                <button type="button" class="btn btn-ghost px-8 font-black uppercase text-[11px] tracking-widest opacity-40 hover:opacity-100">Batal</button>
                <button type="submit" class="btn btn-primary px-10 font-black uppercase text-[11px] tracking-[0.2em] shadow-lg shadow-primary/20">
                    Kirim Permohonan
                </button>
            </div>
        </form>
    </div>
</div>
