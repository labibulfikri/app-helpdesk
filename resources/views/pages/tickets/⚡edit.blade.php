<?php

use Livewire\Component;
use Livewire\WithFileUploads; // Tambahkan ini untuk handle foto
use App\Models\Ticket;
use App\Models\Departement;
use App\Models\Tickethistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public $ticket;

    // Properti Form
    public $category, $tindakan, $resource_type, $problem_detail, $emergency_action, $target_departement_id;
    public $attachment, $old_attachment; // Properti Foto

    public function mount($id) {
        $this->ticket = Ticket::findOrFail($id);

        if ($this->ticket->status != 'pending') {
            return redirect()->route('tickets.details', $this->ticket->id);
        }

        $this->category              = $this->ticket->category;
        $this->tindakan              = $this->ticket->tindakan;
        $this->resource_type         = $this->ticket->resource_type;
        $this->problem_detail        = $this->ticket->problem_detail;
        $this->emergency_action      = $this->ticket->emergency_action;
        $this->target_departement_id = $this->ticket->target_departement_id;
        $this->old_attachment        = $this->ticket->attachment;
    }

    private function generateNewNumber($deptId) {
        $dept = Departement::find($deptId);
        $code = $dept->code;
        $date = now()->format('ymd');
        $count = Ticket::where('target_departement_id', $deptId)
                        ->whereDate('created_at', now())
                        ->count() + 1;

        return $code . '-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function updateRequest() {
        $this->validate([
            'category'              => 'required',
            'tindakan'              => 'required',
            'resource_type'         => 'required',
            'problem_detail'        => 'required|min:5',
            'target_departement_id' => 'required',
            'attachment'            => 'nullable|image|max:2048', // Max 2MB
        ]);

        $oldNumber = $this->ticket->ticket_number;
        $newNumber = ($this->target_departement_id != $this->ticket->target_departement_id)
                     ? $this->generateNewNumber($this->target_departement_id)
                     : $oldNumber;

        // Logic Update Foto
        $attachmentPath = $this->old_attachment;
        if ($this->attachment) {
            // Hapus foto lama jika ada
            if ($this->old_attachment) {
                Storage::disk('public')->delete($this->old_attachment);
            }
            $attachmentPath = $this->attachment->store('tickets', 'public');
        }

        $this->ticket->update([
        'ticket_number'         => $newNumber,
        'category'              => $this->category,
        'tindakan'              => $this->tindakan,
        'resource_type'         => $this->resource_type,
        'description'           => $this->problem_detail,
        'emergency_action'      => $this->emergency_action,
        'target_departement_id' => $this->target_departement_id,
        'attachment'            => $attachmentPath,
    ]);

    // PERBAIKAN DI SINI:
    Tickethistory::create([
        'ticket_id'   => $this->ticket->id,
        'user_id'     => Auth::id(),
        'status_from' => 'pending', // Karena di mount() sudah kita kunci hanya untuk status pending
        'status_to'   => 'pending',
        'comment'     => "User memperbarui detail permohonan & lampiran foto.",
    ]);

    session()->flash('success', 'Permohonan berhasil diperbarui!');
    return redirect()->route('tickets.details', $this->ticket->id);
    }
}; ?>

<div class="min-h-screen bg-slate-50/50 p-4 lg:p-10">
    <div class="max-w-5xl mx-auto">

        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">
            <a href="/tickets" class="hover:text-primary transition-colors">Tickets</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-slate-600">Edit Permohonan</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-white shadow-sm border border-slate-200 rounded-[2rem] overflow-hidden">
                    <div class="p-8 lg:p-10">
                        <div class="flex items-center justify-between mb-10">
                            <div>
                                <h1 class="text-3xl font-black italic tracking-tighter uppercase text-slate-800">Koreksi Data</h1>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Lengkapi detail permohonan Anda</p>
                            </div>
                            <div class="text-right">
                                <div class="badge badge-warning font-black text-[10px] px-4 py-3 rounded-full italic shadow-sm shadow-warning/20 text-warning-content">PENDING STATUS</div>
                            </div>
                        </div>

                        <form wire:submit.prevent="updateRequest" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control w-full">
                                    <label class="label"><span class="label-text font-black text-[10px] uppercase text-slate-500">Kategori Asset</span></label>
                                    <select wire:model="category" class="select select-bordered w-full bg-slate-50 border-slate-200 focus:outline-primary font-bold">
                                        <option value="Mesin">Mesin</option>
                                        <option value="Peralatan">Peralatan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-control w-full">
                                    <label class="label"><span class="label-text font-black text-[10px] uppercase text-slate-500">Jenis Tindakan</span></label>
                                    <select wire:model="tindakan" class="select select-bordered w-full bg-slate-50 border-slate-200 focus:outline-primary font-bold">
                                        <option value="Pemeliharaan">Pemeliharaan</option>
                                        <option value="Pemeriksaan">Pemeriksaan</option>
                                        <option value="Perbaikan">Perbaikan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control w-full">
                                    <label class="label"><span class="label-text font-black text-[10px] uppercase text-slate-500">Resource Type / No. Unit</span></label>
                                    <input type="text" wire:model="resource_type" class="input input-bordered w-full bg-slate-50 border-slate-200 focus:outline-primary font-bold" placeholder="Contoh: CNC-01">
                                </div>
                                <div class="form-control w-full">
                                    <label class="label flex justify-between">
                                        <span class="label-text font-black text-[10px] uppercase text-slate-500">Target Departement</span>
                                        @if($target_departement_id != $ticket->target_departement_id)
                                            <span class="text-[8px] font-black text-error animate-pulse">Ticket No. Will Reset!</span>
                                        @endif
                                    </label>
                                    <select wire:model.live="target_departement_id" class="select select-bordered w-full bg-slate-50 border-slate-200 focus:outline-primary font-bold">
                                        @foreach(Departement::all() as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase text-slate-500">Deskripsi Masalah</span></label>
                                <textarea wire:model="problem_detail" class="textarea textarea-bordered h-32 bg-slate-50 border-slate-200 focus:outline-primary font-medium italic" placeholder="Jelaskan detail kerusakan secara spesifik..."></textarea>
                            </div>

                            <div class="form-control w-full">
                                <label class="label"><span class="label-text font-black text-[10px] uppercase text-error/60 italic font-bold tracking-widest">Emergency Action</span></label>
                                <textarea wire:model="emergency_action" class="textarea textarea-bordered h-24 bg-red-50/30 border-red-100 focus:outline-error font-medium italic" placeholder="Tindakan darurat yang sudah dilakukan..."></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-10 border-t border-slate-100">
                                <a href="{{ route('tickets.index') }}" class="btn btn-ghost font-black uppercase text-[10px] tracking-widest">Batal</a>
                                {{-- <a href="{{ route('tickets.details', $ticket->id) }}" class="btn btn-ghost font-black uppercase text-[10px] tracking-widest">Batal</a> --}}
                                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary px-12 rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] shadow-xl shadow-primary/20 transition-all hover:scale-105 active:scale-95">
                                    Update Permohonan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="card bg-white shadow-sm border border-slate-200 rounded-[2rem] overflow-hidden sticky top-10">
                    <div class="p-8">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Lampiran Foto
                        </h3>

                        <div class="relative w-full aspect-square rounded-3xl overflow-hidden bg-slate-100 border-2 border-dashed border-slate-200 group transition-all hover:border-primary/50 flex items-center justify-center">
                            @if ($attachment)
                                <img src="{{ $attachment->temporaryUrl() }}" class="w-full h-full object-cover shadow-inner">
                            @elseif ($old_attachment)
                                <img src="{{ asset('storage/'.$old_attachment) }}" class="w-full h-full object-cover shadow-inner">
                            @else
                                <div class="text-center p-6">
                                    <p class="text-[9px] font-black uppercase opacity-30 italic">Belum Ada Foto</p>
                                </div>
                            @endif

                            <label class="absolute inset-0 cursor-pointer bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity backdrop-blur-[2px]">
                                <input type="file" wire:model="attachment" class="hidden">
                                <span class="text-white font-black text-[10px] uppercase tracking-widest border border-white/50 px-4 py-2 rounded-full">Ganti Foto</span>
                            </label>
                        </div>

                        <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <ul class="text-[9px] font-bold text-slate-400 space-y-2 uppercase tracking-tighter">
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Format: JPG, PNG, WEBP
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Ukuran Maksimal: 2MB
                                </li>
                            </ul>
                        </div>

                        <div wire:loading wire:target="attachment" class="mt-4 text-[9px] font-black text-primary animate-pulse italic">
                            SEDANG MENGUPLOAD FOTO...
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
