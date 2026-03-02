<?php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Ticket; // Pastikan Model Ticket sudah dibuat
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination, WithFileUploads;

    // State Form
    public $subject, $category, $priority, $description, $attachment;
    public $search = '';

 public function with() {
        $user = Auth::user();
        $query = Ticket::query();

        // LOGIC FILTER BERDASARKAN ROLE
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            // Admin & HRD: Tidak ada filter (melihat semua tiket)
            $query->latest();
        } else {
            // Staff & Teknisi: Hanya melihat yang dilaporkan ATAU yang ditugaskan
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('technician_id', $user->id);
            })->latest();
        }

        // Tambahkan pencarian jika diperlukan
        if ($this->search) {
            $query->where('ticket_number', 'like', "%{$this->search}%");
        }

        return [
            'myTickets' => $query->paginate(10)
        ];
 }
 public function deleteTicket($id)
{
    $ticket = Ticket::findOrFail($id);

    // Cek status secara ketat
    if ($ticket->status !== 'pending') {
        $this->dispatch('alert',
            title: 'Aksi Ditolak!',
            text: 'Hanya tiket berstatus PENDING yang boleh dihapus.',
            icon: 'error'
        );
        return;
    }

    // Hapus file fisik jika ada
    if ($ticket->attachment) {
        \Storage::disk('public')->delete($ticket->attachment);
    }

    $ticket->delete();

    $this->dispatch('alert',
        title: 'Terhapus!',
        text: 'Tiket telah berhasil dihapus dari sistem.',
        icon: 'success'
    );
}

// Tambahkan fungsi ini di dalam class Livewire Anda
public function requestCancel($id)
{
    $ticket = Ticket::findOrFail($id);
    // Hanya tiket status 'proses' yang bisa diajukan batal oleh Admin
    if ($ticket->status === 'proses') {
        $ticket->update(['status' => 'proses_rejected']);

        //update ke history
        \DB::table('ticket_histories')->insert([
            'ticket_id' => $ticket->id,
            'status' => 'proses_rejected',
            'changed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->dispatch('alert', icon: 'info', title: 'DIAJUKAN', text: 'Permohonan batal dikirim ke Superadmin.');
    }
}

public function approveCancel($id)
{
    // Hanya Superadmin yang bisa akses ini (tambahkan proteksi di UI)
    $ticket = Ticket::findOrFail($id);
    $ticket->update(['status' => 'rejected']); // Sesuai kesepakatan: Berhasil batal = Rejected

     //update ke history
     \DB::table('ticket_histories')->insert([
        'ticket_id' => $ticket->id,
        'status_from' => $ticket->status, // Status sebelum diubah
        'status_to' => 'rejected', // Status setelah diubah
        'user_id' => Auth::id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    session()->flash('success', 'Tiket telah dibatalkan.');


}

    public function render() {
        return view('pages.tickets.⚡index')->layout('layouts.app');
    }
}; ?>



    @if (session()->has('ticket_updated'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const data = @json(session('ticket_updated'));
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary px-10 rounded-xl'
            }
        });
    });
</script>
@endif
<div class="max-w-7xl mx-auto p-6 lg:p-10 space-y-8">

     @if (session()->has('success'))
            <div class="alert alert-success font-bold text-xs uppercase text-white shadow-lg border-none"><span>{{ session('success') }}</span></div>

        @endif

         @if (session()->has('error'))
            <div class="alert alert-error font-bold text-xs uppercase text-white shadow-lg border-none"><span>{{ session('error') }}</span></div>
        @endif


    <div class="flex flex-col md:flex-row justify-between items-end gap-4 pb-4 border-b border-base-200">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-1.5 h-8 bg-primary rounded-full"></div>
                <h3 class="font-bold uppercase tracking-tight text-3xl text-base-content">Tiket Saya</h3>
            </div>
            <p class="text-base-content/50 text-[10px] font-bold uppercase tracking-[0.2em] ml-5">Riwayat Pengaduan Layanan Helpdesk</p>
        </div>

        @if (Auth::user()->role !== 'technician')
        <a href="/tickets/create" wire:navigate class="btn btn-primary rounded-xl font-bold text-xs uppercase tracking-widest px-6 shadow-lg shadow-primary/20">
            + Tambah Keluhan
        </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-base-100 p-6 rounded-2xl border border-base-200 shadow-sm flex items-center gap-5 transition-all hover:border-warning/50">
            <div class="w-12 h-12 rounded-xl bg-warning/10 text-warning flex items-center justify-center font-bold">W</div>
            <div>
                <div class="text-[10px] font-bold uppercase text-base-content/40 tracking-widest">Waiting</div>
                <div class="text-2xl font-bold text-base-content">{{ $myTickets->where('status', 'pending')->count() }}</div>
            </div>
        </div>
        <div class="bg-base-100 p-6 rounded-2xl border border-base-200 shadow-sm flex items-center gap-5 transition-all hover:border-info/50">
            <div class="w-12 h-12 rounded-xl bg-info/10 text-info flex items-center justify-center font-bold">P</div>
            <div>
                <div class="text-[10px] font-bold uppercase text-base-content/40 tracking-widest">On proses</div>
                <div class="text-2xl font-bold text-base-content">{{ $myTickets->where('status', 'proses')->count() }}</div>
            </div>
        </div>
        <div class="bg-base-100 p-6 rounded-2xl border border-base-200 shadow-sm flex items-center gap-5 transition-all hover:border-success/50">
            <div class="w-12 h-12 rounded-xl bg-success/10 text-success flex items-center justify-center font-bold">R</div>
            <div>
                <div class="text-[10px] font-bold uppercase text-base-content/40 tracking-widest">Resolved</div>
                <div class="text-2xl font-bold text-base-content">{{ $myTickets->where('status', 'closed')->count() }}</div>
            </div>
        </div>
        <div class="bg-base-100 p-6 rounded-2xl border border-base-200 shadow-sm flex items-center gap-5 transition-all hover:border-error/50">
            <div class="w-12 h-12 rounded-xl bg-error/10 text-error flex items-center justify-center font-bold">R</div>
            <div>
                <div class="text-[10px] font-bold uppercase text-base-content/40 tracking-widest">Rejected</div>
                <div class="text-2xl font-bold text-base-content">{{ $myTickets->where('status', 'rejected')->count() }}</div>
            </div>
        </div>

    </div>

    <div class="bg-base-100 rounded-2xl border border-base-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-base-200 bg-base-50/50">
            <div class="relative w-full md:w-96">
                <input type="text" wire:model.live="search" class="input input-bordered w-full rounded-xl pl-12 h-11 bg-base-100 focus:border-primary transition-all text-sm" placeholder="Cari tiket...">
                <svg class="w-4 h-4 absolute left-4 top-3.5 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200/50 text-base-content/50 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="pl-8 py-4">Informasi Tiket</th>
                        <th>Status Tracking</th>
                        <th class="text-right pr-8">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-base-content">
                    @foreach($myTickets as $ticket)
                    <tr class="hover:bg-base-200/20 transition-colors">
                        <td class="pl-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-base-200 text-base-content/40 rounded-lg flex items-center justify-center font-bold text-[10px] border border-base-300">
                                    #{{ substr($ticket->ticket_number, -3) }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm uppercase tracking-tight">{{ $ticket->ticket_number }}</div>
                                    <div class="text-[9px] text-base-content/40 font-bold uppercase tracking-wider mt-0.5">
                                        Dibuat {{ $ticket->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @php
                                $statusStyle = match($ticket->status) {
                                    'pending' => 'badge-info text-info',
                                    'proses' => 'badge-info text-info',
                                    'cancel_pending' => 'badge-warning text-warning',
                                    'closed' => 'badge-success text-success',
                                    'rejected' => 'badge-ghost opacity-50',
                                    default => 'badge-ghost',
                                };
                            @endphp
                            <div class="inline-flex px-3 py-1 rounded-md border font-bold text-[9px] uppercase tracking-widest {{ $statusStyle }}">
                                {{ $ticket->status }}
                            </div>
                        </td>

                        <td class="text-right pr-8">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('tickets.details', $ticket->id) }}"
                                   class="btn btn-square btn-ghost btn-xs text-info" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                @php
                                    $canEdit = false;
                                    if(Auth::id() == $ticket->user_id && $ticket->status == 'pending') $canEdit = true;
                                    if(in_array(Auth::user()->role, ['admin', 'hrd']) && $ticket->status != 'closed') $canEdit = true;
                                    if(Auth::id() == $ticket->technician_id && $ticket->status == 'proses') $canEdit = true;
                                @endphp

                                @if (Auth::user()->role === 'staff' && $canEdit)
                                    <a href="{{ route('tickets.edit', $ticket->id) }}"
                                       class="btn btn-square btn-ghost btn-xs text-warning" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                @endif
{{-- @if(Auth::user()->role === 'admin'   && $ticket->status === 'proses')
                                    <button onclick="confirmRequestCancel({{ $ticket->id }})" class="btn btn-ghost btn-xs text-warning font-bold uppercase text-[9px]">Batalkan?</button>
                                @endif --}}

                                {{-- @if(Auth::user()->role === 'superadmin' && $ticket->status === 'proses')
                                    <button wire:click="approveCancel({{ $ticket->id }})" class="btn btn-success btn-xs text-white font-bold uppercase text-[9px]">Setujui Batal</button>
                                @endif


                                @if(Auth::user()->role === 'superadmin' && $ticket->status === 'proses_rejected')
                                    <button wire:click="approveCancel({{ $ticket->id }})" class="btn btn-success btn-xs text-white font-bold uppercase text-[9px]">Setujui Batal</button>
                                @endif --}}
                                @if($ticket->status == 'closed')
                                    <a href="{{ route('tickets.print', $ticket->id) }}" target="_blank"
                                       class="btn btn-square btn-ghost btn-xs text-error" title="Cetak">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    </a>
                                @endif

                                @if(Auth::id() == $ticket->user_id && $ticket->status == 'pending')
                                    <button type="button" onclick="confirmDelete({{ $ticket->id }})"
                                            class="btn btn-square btn-ghost btn-xs text-error" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-base-200 bg-base-50/30">
            {{ $myTickets->links() }}
        </div>
    </div>
</div>


<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Tiket?',
            text: "Data tidak bisa dikembalikan setelah dihapus.",
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
                @this.call('deleteTicket', id);
            }
        })
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('alert', (data) => {
            const res = Array.isArray(data) ? data[0] : data;
            Swal.fire({
                title: res.title,
                text: res.text,
                icon: res.icon,
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-xl'
                }
            });
        });
    });
</script>
<script>
    // Fungsi Request Batal (Admin)
    function confirmRequestCancel(id) {
        Swal.fire({
            title: 'Ajukan Pembatalan?',
            text: "Tiket akan dikirim ke Superadmin untuk disetujui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ajukan',
            customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg uppercase text-xs font-bold px-6', cancelButton: 'rounded-lg uppercase text-xs font-bold px-6' }
        }).then((result) => {
            if (result.isConfirmed) { @this.call('requestCancel', id); }
        })
    }

    // Fungsi Hapus (Global)
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Tiket?',
            text: "Tindakan ini permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg uppercase text-xs font-bold px-6', cancelButton: 'rounded-lg uppercase text-xs font-bold px-6' }
        }).then((result) => {
            if (result.isConfirmed) { @this.call('deleteTicket', id); }
        })
    }
</script>
