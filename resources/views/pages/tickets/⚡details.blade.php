<?php

use Livewire\Component;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Tickethistory;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $ticket;
    public $alasan_pembatalan;

    // Properti Modal Admin
    public $selected_technician, $action_plan, $schedule_date, $status_admin;

    // Properti Modal Teknisi
    public $damage_analysis, $temporary_action, $permanent_action, $preventive_action, $status_tech;

    public function mount($id) {
        $this->loadTicket($id);
        $this->syncData();
    }

    public function loadTicket($id) {
        $this->ticket = Ticket::with(['user', 'aset','target_departement', 'technician', 'histories.user'])->findOrFail($id);
    }

    public function syncData() {
        $this->selected_technician = $this->ticket->technician_id;
        $this->action_plan         = $this->ticket->action_plan;
        $this->schedule_date       = $this->ticket->schedule_date;
        $this->status_admin        = $this->ticket->status;

        $this->damage_analysis     = $this->ticket->damage_analysis;
        $this->temporary_action    = $this->ticket->temp_action;
        $this->permanent_action    = $this->ticket->perm_action;
        $this->preventive_action   = $this->ticket->preventive_action;
        $this->status_tech         = $this->ticket->status;

        $this->alasan_pembatalan = $this->ticket->alasan_pembatalan;

    }

// public function requestCancellation()
// {
//     // 1. Validasi input
//     $this->validate([
//         'alasan_pembatalan' => 'required',
//     ]);

//     try {
//         \DB::transaction(function () {
//             $ticket = Ticket::findOrFail($this->ticket->id);

//             // 2. Update status dan simpan alasan ke kolom database Anda
//             // Asumsi nama kolom di database Anda adalah 'alasan_pembatalan'
//             $ticket->update([
//                 'status' => 'proses_rejected',
//                 'alasan_pembatalan' => $this->alasan_pembatalan
//             ]);

//             // 3. Catat ke History
//             Tickethistory::create([
//                 'ticket_id' => $ticket->id,
//                 'user_id'   => Auth::id(),
//                 'status_from' => $this->ticket->status, // Tambahkan status_from agar konsisten dengan logHistory Anda
//                 'status_to' => 'proses_rejected',
//                 'comment'   => 'Permohonan Pembatalan: ' . $this->alasan_pembatalan,
//             ]);
//         });
// $this->loadTicket($this->ticket->id);
//         // 4. Tutup modal (menggunakan JS dispatch) dan beri notifikasi
//         $this->dispatch('close-modal');
//         $this->dispatch('success', title: 'Berhasil!', text: 'Permohonan pembatalan sedang ditinjau Superadmin.', icon: 'success');
//          // Reset input
//         $this->alasan_pembatalan = '';

//     } catch (\Exception $e) {
//         $this->dispatch('error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.', icon: 'error');
//     }
// }

public function requestCancellation()
{
    $this->validate(['alasan_pembatalan' => 'required']);

    try {
        $prevStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => 'proses_rejected',
            'alasan_pembatalan' => $this->alasan_pembatalan
        ]);

        // PENERIMA: Para Admin/Superadmin untuk meninjau pembatalan
        $receivers = User::whereIn('role', ['admin', 'superadmin'])->pluck('id')->toArray();

        $this->logHistory($prevStatus, 'proses_rejected', 'Permohonan Pembatalan: ' . $this->alasan_pembatalan, $receivers);

        $this->dispatch('close-modal');
        $this->dispatch('success', title: 'Berhasil!', text: 'Permohonan pembatalan sedang ditinjau Superadmin.', icon: 'success');
        $this->alasan_pembatalan = '';

    } catch (\Exception $e) {
        $this->dispatch('error', title: 'Gagal!', text: 'Terjadi kesalahan sistem.', icon: 'error');
    }
}

// public function decideCancellation($decision) {
//         if (Auth::user()->role !== 'superadmin') return;

//         try {
//             DB::transaction(function () use ($decision) {
//                 $ticket = Ticket::findOrFail($this->ticket->id);
//                 $prevStatus = $ticket->status;

//                 if ($decision === 'approve') {
//                     $newStatus = 'rejected';
//                     $msg = "Superadmin menyetujui pembatalan tiket.";
//                 } else {
//                     $newStatus = 'proses';
//                     $msg = "Superadmin menolak pembatalan. Tiket dilanjutkan.";
//                 }

//                 $ticket->update(['status' => $newStatus, 'alasan_pembatalan' => ($decision === 'approve') ? $this->alasan_pembatalan : null ]);
//                 $this->logHistory($prevStatus, $newStatus, $msg);
//             });

//             $this->dispatch('close-modal');
//             $this->dispatch('success', title: 'Berhasil', text: 'Keputusan telah disimpan.', icon: 'success');
//         } catch (\Exception $e) {
//             $this->dispatch('error', title: 'Error', text: 'Gagal memproses keputusan.', icon: 'error');
//         }
//     }

public function decideCancellation($decision) {
    if (Auth::user()->role !== 'superadmin') return;

    try {
        $prevStatus = $this->ticket->status;

        if ($decision === 'approve') {
            $newStatus = 'rejected';
            $msg = "Superadmin menyetujui pembatalan tiket.";
        } else {
            $newStatus = 'proses';
            $msg = "Superadmin menolak pembatalan. Tiket dilanjutkan.";
        }

        $this->ticket->update([
            'status' => $newStatus,
            'alasan_pembatalan' => ($decision === 'approve') ? $this->alasan_pembatalan : null
        ]);

        // PENERIMA: Staff Pembuat Tiket & Teknisi terkait
        $receivers = [$this->ticket->user_id, $this->ticket->technician_id];

        $this->logHistory($prevStatus, $newStatus, $msg, array_filter($receivers));

        $this->dispatch('close-modal');
        $this->dispatch('success', title: 'Berhasil', text: 'Keputusan telah disimpan.', icon: 'success');
    } catch (\Exception $e) {
        $this->dispatch('error', title: 'Error', text: 'Gagal memproses keputusan.', icon: 'error');
    }
}

    public function updateByAdmin() {
        if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) return;

        $this->validate([
            'selected_technician' => 'required',
            'action_plan'         => 'required|min:5',
            'schedule_date'       => 'required',
        ]);

        $prevStatus = $this->ticket->status;
        // Jika admin update dari pending, otomatis jadi proses
        $newStatus  = ($this->status_admin == 'pending') ? 'proses' : $this->status_admin;

        $this->ticket->update([
            'technician_id' => $this->selected_technician,
            'action_plan'   => $this->action_plan,
            'schedule_date' => $this->schedule_date,
            'status'        => $newStatus
        ]);

        // $techName = User::find($this->selected_technician)->name;
        // $role = Auth::user()->role;
        // $this->logHistory($prevStatus, $newStatus, " .$role. memperbarui penugasan ke: $techName.");

        // $this->dispatch('close-modal');
        // session()->flash('success', 'Data penugasan berhasil diperbarui.');
        $tech = User::find($this->selected_technician);
    $role = Auth::user()->role;
    $msg  = ucfirst($role) . " memperbarui penugasan ke: " . $tech->name;

    // --- BAGIAN YANG DIUBAH ---
    // Tentukan siapa yang dapat notifikasi ini:
    // 1. Staff Pembuat Tiket ($this->ticket->user_id)
    // 2. Teknisi yang baru ditunjuk ($this->selected_technician)
    $receivers = [
        $this->ticket->user_id,
        $this->selected_technician
    ];

    $this->logHistory($prevStatus, $newStatus, $msg, $receivers);
    // ---------------------------

    $this->dispatch('close-modal');
    $this->dispatch('show-alert', [
        'title' => 'Berhasil!',
        'text'  => 'Data penugasan berhasil diperbarui.',
        'icon'  => 'success'
    ]);
    }

    // public function updateByTechnician() {
    //     if (Auth::id() !== $this->ticket->technician_id) return;

    //     $prevStatus = $this->ticket->status;

    //     $this->ticket->update([
    //         'damage_analysis'   => $this->damage_analysis,
    //         'temp_action'       => $this->temporary_action,
    //         'perm_action'       => $this->permanent_action,
    //         'preventive_action' => $this->preventive_action,
    //         'status'            => $this->status_tech,
    //         'completion_date'   => ($this->status_tech == 'done') ? now() : $this->ticket->completion_date,
    //     ]);

    //     $role = Auth::user()->role;
    //     $msg = ($this->status_tech == 'done') ? " .$role. menyelesaikan pekerjaan." : " .$role. memperbarui progres laporan.";
    //     $this->logHistory($prevStatus, $this->status_tech, $msg);

    //     $this->dispatch('close-modal');
    //     session()->flash('success', 'Laporan teknisi berhasil diperbarui.');
    // }

    public function updateByTechnician() {
    if (Auth::id() !== $this->ticket->technician_id) return;

    $prevStatus = $this->ticket->status;

    $this->ticket->update([
        'damage_analysis'   => $this->damage_analysis,
        'temp_action'       => $this->temporary_action,
        'perm_action'       => $this->permanent_action,
        'preventive_action' => $this->preventive_action,
        'status'            => $this->status_tech,
        'completion_date'   => ($this->status_tech == 'done') ? now() : $this->ticket->completion_date,
    ]);

    $role = Auth::user()->role;
    $msg = ($this->status_tech == 'done') ? " .$role. menyelesaikan pekerjaan." : " .$role. memperbarui progres laporan.";

    // PENERIMA: Staff Pembuat Tiket & Semua Admin
    $receivers = User::whereIn('role', ['admin', 'superadmin'])->pluck('id')->toArray();
    $receivers[] = $this->ticket->user_id;

    $this->logHistory($prevStatus, $this->status_tech, $msg, array_unique($receivers));

    $this->dispatch('close-modal');
    session()->flash('success', 'Laporan teknisi berhasil diperbarui.');
}

//     public function closeTicket() {
//         $role = Auth::user()->role;
//         if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) return;

//         $prevStatus = $this->ticket->status;
//         $this->ticket->update(['status' => 'closed']);

//         $this->logHistory($prevStatus, 'closed', " .$role. menyetujui hasil dan menutup tiket secara permanen.");
// $this->dispatch('show-alert', [
//         'title' => 'Tiket Dikunci!',
//         'text'  => 'Laporan telah berhasil ditutup secara permanen.',
//         'icon'  => 'success'
//     ]);
//         // session()->flash('success', 'Tiket telah ditutup (Closed) & dikunci.');
//     }
public function closeTicket() {
    if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) return;

    $prevStatus = $this->ticket->status;
    $this->ticket->update(['status' => 'closed']);

    // PENERIMA: Staff Pembuat Tiket & Teknisi yang mengerjakan
    $receivers = [$this->ticket->user_id, $this->ticket->technician_id];

    $this->logHistory($prevStatus, 'closed', "Admin menyetujui hasil dan menutup tiket secara permanen.", array_filter($receivers));

    $this->dispatch('show-alert', [
        'title' => 'Tiket Dikunci!',
        'text'  => 'Laporan telah berhasil ditutup secara permanen.',
        'icon'  => 'success'
    ]);
}

    // public function logHistory($from, $to, $msg) {
    //     Tickethistory::create([
    //         'ticket_id'   => $this->ticket->id,
    //         'user_id'     => Auth::id(),
    //         'status_from' => $from,
    //         'status_to'   => $to,
    //         'comment'     => $msg,
    //     ]);
    //     $this->loadTicket($this->ticket->id);
    // }

    public function logHistory($from, $to, $msg, $receivers = []) {
    // 1. Selalu buat satu baris log umum (untuk riwayat audit)
    // Baris ini tidak memiliki received_id (opsional) atau ditandai sebagai log sistem

    // 2. Buat notifikasi spesifik untuk setiap penerima agar is_read tidak bentrok
    foreach ($receivers as $receiverId) {
        if (!$receiverId) continue;

        Tickethistory::create([
            'ticket_id'   => $this->ticket->id,
            'user_id'     => Auth::id(), // Aktor (siapa yang update)
            'received_id' => $receiverId, // Penerima (siapa yang dapat notif)
            'status_from' => $from,
            'status_to'   => $to,
            'comment'     => $msg,
            'is_read'     => false,
        ]);
    }

    $this->loadTicket($this->ticket->id);
}

    public function render() {
        return view('pages.tickets.⚡details')->layout('layouts.app');
    }
}; ?>

<div class="min-h-screen bg-base-200/50 p-4 lg:p-10" x-data="{ modalSuperDecision:false,modalNonDecision:false, modalAdmin: false, modalTech: false, modalRejected:false }" @close-modal.window="modalAdmin = false; modalTech = false; modalSuperDecision = false; modalRejected=false; modalNonDecision=false ">

    @if($ticket->status == 'rejected' || $ticket->status == 'proses_rejected')
        <div class="alert alert-error font-bold text-sm  text-white shadow-lg border-none mb-6">
            <span>Tiket ini dibatalkan oleh {{ $ticket->status == 'rejected' ? 'Admin' : 'User' }}. Alasan: "{{ $ticket->alasan_pembatalan }}". @if($ticket->status == 'proses_rejected') Menunggu keputusan Superadmin. @endif</span>
        </div>
    @endif


    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex flex-wrap justify-between items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-base-300">
            <div class="flex items-center gap-4">
                <a href="/tickets" wire:navigate class="btn btn-circle btn-sm btn-ghost border border-base-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-black italic uppercase tracking-tighter">{{ $ticket->ticket_number }}</h1>
                    <div class="badge {{ $ticket->status == 'closed' ? 'badge-neutral' : 'badge-primary' }} font-bold text-[10px] uppercase">{{ $ticket->status }}</div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($ticket->status == 'closed')
                    <a href="{{ route('tickets.print', $ticket->id) }}" target="_blank" class="btn btn-sm btn-error text-white font-black uppercase text-[10px] px-6 shadow-lg shadow-error/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Cetak PDF PPP
                    </a>
                @endif

                @if(in_array(Auth::user()->role, ['admin', 'superadmin']) && $ticket->status == 'done')
                    {{-- <button wire:click="closeTicket" onclick="return confirm('Tutup permanen? Laporan tidak bisa diubah lagi.')" class="btn btn-sm btn-success text-white font-black uppercase text-[10px] px-6">Close & Lock</button> --}}
                    <button
    type="button"
    @click="
        Swal.fire({
            title: 'Tutup Tiket?',
            text: 'Laporan akan dikunci secara permanen dan tidak bisa diubah lagi!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#22c55e',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'YA, TUTUP & KUNCI!',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'btn btn-success rounded-xl font-black px-6',
                cancelButton: 'btn btn-ghost rounded-xl font-black px-6'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.closeTicket(); // Memanggil fungsi di Livewire
            }
        })
    "
    class="btn btn-sm btn-success text-white font-black uppercase text-[10px] px-6">
    Close & Lock
</button>
                @endif

                @if(in_array(Auth::user()->role, ['admin', 'superadmin']) && $ticket->status != 'closed' && $ticket->status != 'proses_rejected' && $ticket->status != 'rejected')
                    <button @click="modalAdmin = true" class="btn btn-sm btn-outline btn-primary font-black uppercase text-[10px]">Edit Penugasan</button>
                @endif



                @if(in_array(Auth::user()->role, ['admin']) && $ticket->status == 'proses')
                    <button @click="modalRejected = true"  class="btn btn-sm btn-warning text-white font-black uppercase text-[10px]">Ajukan Pembatalan Tiket</button>
                @endif


                @if(in_array(Auth::user()->role, ['superadmin']) && $ticket->status == 'proses_rejected')
                    <button @click="modalSuperDecision = true"  class="btn btn-sm btn-warning text-white font-black uppercase text-[10px]">Tanggapi</button>
                @endif
                @if(in_array(Auth::user()->role, ['superadmin']) && $ticket->status == 'proses' || $ticket->status == 'done')

                    <button @click="modalNonDecision = true"  class="btn btn-sm btn-warning text-white font-black uppercase text-[10px]">Batalkan </button>
                @endif




                @if(Auth::id() === $ticket->technician_id && $ticket->status == 'proses')
                    <button @click="modalTech = true" class="btn btn-sm btn-success text-white font-black uppercase text-[10px]">Update Laporan</button>
                @endif



            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success font-bold text-xs uppercase text-white shadow-lg border-none"><span>{{ session('success') }}</span></div>

        @endif

         @if (session()->has('error'))
            <div class="alert alert-error font-bold text-xs uppercase text-white shadow-lg border-none"><span>{{ session('error') }}</span></div>
        @endif


        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 space-y-6">
                <div class="card bg-white shadow-sm border border-base-300 overflow-hidden">
                    <div class="bg-base-100 px-8 py-4 border-b border-base-300 flex justify-between">
                        <span class="text-[10px] font-black uppercase opacity-40 italic">I. Diisi Oleh Pengguna</span>
                        <span class="text-[10px] font-bold opacity-40 uppercase">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 text-sm">
                            <div><label class="block opacity-40 font-bold text-[9px] uppercase">Aset</label><p class="font-bold">{{ $ticket->aset->nama_aset ?? ' -' }}</p></div>
                            <div><label class="block opacity-40 font-bold text-[9px] uppercase">Jenis</label><p class="font-bold">{{ $ticket->category }}</p></div>
                            <div><label class="block opacity-40 font-bold text-[9px] uppercase">Tindakan</label><p class="font-bold italic uppercase">{{ $ticket->tindakan }}</p></div>
                            <div><label class="block opacity-40 font-bold text-[9px] uppercase">Kode PPP</label><p class="font-bold">{{ $ticket->kode_ppp }}</p></div>
                        </div>
                        <div class="space-y-4">
                            <label class="block opacity-40 font-bold text-[9px] uppercase">Problem Detail</label>
                            <div class="p-4 bg-base-200/50 border border-dashed rounded-xl italic text-sm">"{{ $ticket->problem_detail }}"</div>

                        </div>
                         <div class="space-y-4">
                            <label class="block opacity-40 font-bold text-[9px] uppercase mb-1">Tindakan Darurat yang Telah Dilakukan</label>
                             <div class="p-4 bg-base-200/50 border border-dashed rounded-xl italic text-sm">"{{ $ticket->emergency_action }}"</div>

                        </div>
                       <div class="mt-4 collapse bg-base-100  collapse-arrow  border-base-300 border">
                        <input type="checkbox" />
                        <div class="collapse-title font-semibold">Lampiran Foto</div>
                        <div class=" collapse-content space-y-4">
                            <label class="block opacity-40 font-bold text-[9px] uppercase mb-1">Lampiran Foto</label>
                            @if($ticket->attachment)
                                <img src="{{ asset('storage/' . $ticket->attachment) }}" class="w-full rounded-2xl border border-base-300 shadow-sm" alt="Evidence">
                            @else
                                <div class="p-4 bg-base-200/50 border border-dashed rounded-xl italic text-sm text-center">Tidak ada lampiran foto.</div>
                            @endif
                    </div>

                    </div>
                    </div>
                </div>


                @if($ticket->technician_id)
                <div class="card bg-white shadow-sm border border-primary/20 overflow-hidden border-l-8 border-l-primary">
                    <div class="bg-primary/5 px-8 py-4 border-b border-primary/10">
                        <span class="text-[10px] font-black uppercase text-primary italic">II. Diisi Oleh Plant Engineering / Maintenance</span>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-2 gap-6 text-sm mb-6">
                            <div class="p-4 bg-primary/5 rounded-xl border border-primary/10">
                                <label class="text-[8px] font-bold uppercase block opacity-50 mb-1">Teknisi Pelaksana</label>
                                <span class="font-black uppercase italic text-primary">{{ $ticket->technician->name }}</span>
                            </div>
                            <div class="p-4 bg-primary/5 rounded-xl border border-primary/10">
                                <label class="text-[8px] font-bold uppercase block opacity-50 mb-1">Jadwal Pelaksanaan</label>
                                <span class="font-black italic text-primary">{{ \Carbon\Carbon::parse($ticket->schedule_date)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        <div class="p-4 bg-base-100 border border-dashed rounded-xl text-sm italic">
                            <label class="text-[8px] font-bold uppercase block opacity-50 mb-1">Rencana Tindakan Yang Diambil</label>
                            "{{ $ticket->action_plan }}"
                        </div>
                    </div>
                </div>
                @endif

                @if($ticket->damage_analysis)
                <div class="card bg-white shadow-sm border border-success/20 overflow-hidden border-l-8 border-l-success">
                    <div class="bg-success/5 px-8 py-4 border-b border-success/10">
                        <span class="text-[10px] font-black uppercase text-success italic">III. Diisi Oleh Teknisi Pelaksana</span>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div><label class="text-[9px] font-black uppercase text-success opacity-60">Analisa Penyebab</label><p class="text-sm italic font-medium">"{{ $ticket->damage_analysis }}"</p></div>
                            <div><label class="text-[9px] font-black uppercase text-success opacity-60">Tindakan Sementara</label><p class="text-sm italic font-medium">"{{ $ticket->temp_action }}"</p></div>
                        </div>
                        <div class="space-y-4">
                            <div><label class="text-[9px] font-black uppercase text-success opacity-60">Tindakan Permanen</label><p class="text-sm italic font-medium">"{{ $ticket->perm_action }}"</p></div>
                            <div><label class="text-[9px] font-black uppercase text-success opacity-60">Tindakan Pencegahan</label><p class="text-sm italic font-medium">"{{ $ticket->preventive_action }}"</p></div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- <div class="lg:col-span-4">
                <div class="card bg-neutral text-neutral-content p-8 shadow-xl h-fit sticky top-10 rounded-3xl border-none">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] mb-10 text-center opacity-40 italic underline decoration-primary underline-offset-8">Activity History Log</h3>
                    <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-white/10">
                        @foreach($ticket->histories->sortByDesc('created_at') as $log)
                        <div class="relative flex items-start gap-6 group">
                            <div class="absolute left-0 w-10 h-10 rounded-full border border-white/10 bg-neutral flex items-center justify-center z-10">
                                <div class="w-1.5 h-1.5 bg-primary rounded-full group-hover:scale-150 transition-transform"></div>
                            </div>
                            <div class="ml-10 p-4 rounded-2xl border border-white/5 bg-white/5 w-full hover:bg-white/10 transition-colors">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[9px] font-black text-primary uppercase italic">{{ $log->status_to }}</span>
                                    <span class="text-[8px] opacity-40 font-mono">{{ $log->created_at->format('H:i') }}</span>
                                </div>
                                <p class="text-[11px] font-medium leading-relaxed opacity-80 italic">"{{ $log->comment }}"</p>
                                <p class="text-[8px] mt-2 font-black uppercase tracking-widest opacity-20">By: {{ $log->user->name }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div> --}}
            <div class="lg:col-span-4">
    <div class="card bg-base-100 shadow-2xl h-fit sticky top-10 rounded-[2.5rem] border border-base-content/5 overflow-hidden">
        <div class="bg-base-content p-6 mb-6">
            <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-center text-base-100 italic">
                Activity History Log
            </h3>
        </div>

        <div class="card-body p-8 pt-0">
            <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-base-content/10">

                @foreach($ticket->histories->sortByDesc('created_at') as $log)
                <div class="relative flex items-start gap-6 group">
                    <div class="absolute left-0 w-10 h-10 rounded-2xl border border-base-content/5 bg-base-200 flex items-center justify-center z-10 shadow-sm">
                        <div class="w-2 h-2 bg-primary rounded-full group-hover:scale-150 transition-all duration-300 shadow-[0_0_10px_rgba(var(--p),0.5)]"></div>
                    </div>

                    <div class="ml-10 p-5 rounded-3xl border border-base-content/5 bg-base-200/50 w-full hover:bg-base-200 transition-all duration-300 group-hover:translate-x-1">
                        <div class="flex justify-between items-center mb-2">
                            <span class="badge badge-primary badge-outline font-black text-[8px] uppercase italic px-3 py-2">
                                {{ $log->status_to }}
                            </span>
                            <span class="text-[9px] opacity-40 font-black tracking-tighter uppercase">
                                {{ $log->created_at->format('H:i') }} • {{ $log->created_at->format('d M') }}
                            </span>
                        </div>

                        <p class="text-[11px] font-bold leading-relaxed text-base-content/70 italic">
                            "{{ $log->comment }}"
                        </p>

                        <div class="flex items-center gap-2 mt-4 pt-3 border-t border-base-content/5">
                            <div class="w-4 h-4 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-[7px] font-black text-primary">{{ substr($log->user->name, 0, 1) }}</span>
                            </div>
                            <p class="text-[8px] font-black uppercase tracking-widest opacity-30">
                                Updated By: {{ $log->user->name }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
        </div>



        <div class="modal" :class="{ 'modal-open': modalSuperDecision }">
            <div class="modal-box rounded-[2.5rem] p-8 border-4 border-warning">
                <h3 class="font-black uppercase italic text-2xl text-center mb-6">Keputusan Superadmin</h3>
                <div class="bg-base-200 p-6 rounded-2xl mb-8">
                    <p class="text-[10px] font-black uppercase opacity-40 mb-2">Alasan User:</p>
                    <p class="font-bold italic">"{{ $ticket->alasan_pembatalan }}"</p>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <button wire:click="decideCancellation('approve')" class="btn btn-error h-16 rounded-2xl font-black uppercase tracking-widest">🔥 Setujui & Batalkan Tiket</button>
                    <button wire:click="decideCancellation('reject')" class="btn btn-outline h-16 rounded-2xl font-black uppercase tracking-widest">❌ Tolak Pembatalan (Tetap Proses)</button>
                </div>
            </div>
        </div>
        <div class="modal" :class="{ 'modal-open': modalNonDecision }">
            <div class="modal-box rounded-[2.5rem] p-8 border-4 border-warning">
                <button @click="modalNonDecision = false" class="btn btn-sm btn-circle btn-ghost absolute right-5 top-5 font-black">✕</button>
                <h3 class="font-black uppercase italic text-2xl text-center mb-6">Keputusan Superadmin</h3>
                <div class="bg-base-200 p-6 rounded-2xl mb-8">
                    <p class="text-[10px] font-black uppercase opacity-40 mb-2">Alasan:</p>
                    <input type="text" wire:model="alasan_pembatalan" class="input input-bordered w-full font-bold focus:input-primary" placeholder="Masukkan alasan pembatalan tiket...">
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <button wire:click="decideCancellation('approve')" class="btn btn-error h-16 rounded-2xl font-black uppercase tracking-widest">🔥 Setujui & Batalkan Tiket</button>
                    {{-- <button wire:click="decideCancellation('reject')" class="btn btn-outline h-16 rounded-2xl font-black uppercase tracking-widest">❌ Tolak Pembatalan (Tetap Proses)</button> --}}
                </div>
            </div>
        </div>
        <div class="modal" :class="{ 'modal-open': modalAdmin }">
            <div class="modal-box max-w-lg bg-white p-0 overflow-hidden rounded-3xl border-none shadow-2xl">
                <div class="bg-primary p-6 text-white flex justify-between items-center">
                    <h3 class="font-black uppercase italic tracking-tighter text-xl">Edit Distribusi Penugasan</h3>
                    <button @click="modalAdmin = false" class="btn btn-sm btn-circle btn-ghost text-white">✕</button>
                </div>
                <form wire:submit.prevent="updateByAdmin" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label font-bold text-[10px] uppercase opacity-60">Pilih Teknisi</label>
                            <select wire:model="selected_technician" class="select select-bordered font-bold focus:select-primary">
                                <option value="">-- Pilih --</option>
                                @foreach(User::where('role', 'technician')->get() as $tech) <option value="{{ $tech->id }}">{{ $tech->name }}</option> @endforeach
                            </select>

                            <span class="text-sm text-error mt-1">@error('selected_technician') {{ $message }} @enderror</span>

                        </div>
                        <div class="form-control">
                            <label class="label font-bold text-[10px] uppercase opacity-60">Target Selesai</label>
                            <input type="date" wire:model="schedule_date" class="input input-bordered font-bold focus:input-primary">
                            <span class="text-sm text-error mt-1">@error('schedule_date') {{ $message }} @enderror</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label font-bold text-[10px] uppercase opacity-60">Instruksi / Rencana Tindakan</label>
                        <textarea wire:model="action_plan" class="textarea textarea-bordered h-24 italic focus:textarea-primary"></textarea>
                        <span class="text-sm text-error mt-1">@error('action_plan') {{ $message }} @enderror</span>
                    </div>
                    <div class="form-control">
                        <label class="label font-bold text-[10px] uppercase opacity-60 text-primary">Status Penugasan</label>
                        <select wire:model="status_admin" class="select select-bordered font-black text-primary uppercase italic">
                            <option value="pending">PENDING (BACK TO QUEUE)</option>
                            <option value="proses">proses (IN PROGRESS)</option>
                        </select>
                        <span class="text-sm text-error mt-1">@error('status_admin') {{ $message }} @enderror</span>
                    </div>
                    <div class="modal-action">
                        <button type="submit" class="btn btn-primary w-full font-black uppercase text-xs tracking-widest">Update Penugasan</button>
                    </div>
                </form>
            </div>
        </div>




        <div class="modal" :class="{ 'modal-open': modalRejected }">
            <div class="modal-box max-w-lg bg-white p-0 overflow-hidden rounded-3xl border-none shadow-2xl">
                <div class="bg-primary p-6 text-white flex justify-between items-center">
                    <h3 class="font-black uppercase italic tracking-tighter text-xl">Alasan Pembatalan</h3>
                    <button @click="modalRejected = false" class="btn btn-sm btn-circle btn-ghost text-white">✕</button>
                </div>
                <form wire:submit.prevent="requestCancellation" class="p-8 space-y-6">

                    <div class="form-control">
                        <label class="label font-bold text-[10px] uppercase opacity-60">Alasan Pembatalan</label>
                        <textarea wire:model="alasan_pembatalan" class="textarea textarea-bordered h-24 italic focus:textarea-primary"></textarea>
                    </div>

                    <div class="modal-action">
                        <button type="submit" class="btn btn-primary w-full font-black uppercase text-xs tracking-widest">Ajukan Pembatalan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" :class="{ 'modal-open': modalTech }">
            <div class="modal-box max-w-3xl bg-white p-0 overflow-hidden rounded-3xl border-none shadow-2xl">
                <div class="bg-success p-6 text-white flex justify-between items-center">
                    <h3 class="font-black uppercase italic tracking-tighter text-xl">Form Laporan Hasil Kerja</h3>
                    <button @click="modalTech = false" class="btn btn-sm btn-circle btn-ghost text-white">✕</button>
                </div>
                <form wire:submit.prevent="updateByTechnician" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control"><label class="label font-bold text-[10px] uppercase opacity-60">Analisa Penyebab</label><textarea wire:model="damage_analysis" class="textarea textarea-bordered h-24 focus:textarea-success"></textarea></div>
                        <div class="form-control"><label class="label font-bold text-[10px] uppercase opacity-60">Tindakan Sementara</label><textarea wire:model="temporary_action" class="textarea textarea-bordered h-24 focus:textarea-success"></textarea></div>
                        <div class="form-control"><label class="label font-bold text-[10px] uppercase opacity-60">Tindakan Permanen</label><textarea wire:model="permanent_action" class="textarea textarea-bordered h-24 focus:textarea-success"></textarea></div>
                        <div class="form-control"><label class="label font-bold text-[10px] uppercase opacity-60">Tindakan Pencegahan</label><textarea wire:model="preventive_action" class="textarea textarea-bordered h-24 focus:textarea-success"></textarea></div>
                    </div>
                    <div class="divider"></div>
                    <div class="form-control max-w-xs"><label class="label font-bold text-[10px] uppercase text-success">Update Progres</label>
                        <select wire:model="status_tech" class="select select-bordered font-black text-success uppercase italic focus:select-success">
                            <option value="proses">MASIH PROSES / REVISI</option>
                            <option value="done">DONE (FINISH & KIRIM KE ADMIN)</option>
                        </select>
                    </div>
                    <div class="modal-action">
                        <button type="submit" class="btn btn-success text-white w-full font-black uppercase text-xs tracking-widest shadow-lg shadow-success/20">Simpan Laporan & Update Status</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('show-alert', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary px-10 rounded-xl font-bold uppercase text-xs'
                },
                buttonsStyling: false
            });
        });
    });
</script>
