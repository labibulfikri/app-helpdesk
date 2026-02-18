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
        if ($user->role === 'admin' || $user->role === 'hrd') {
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


    public function render() {
        return view('pages.tickets.⚡index')->layout('layouts.app');
    }
}; ?>
<div>
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-end gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-2 h-8 bg-primary rounded-full"></div>
                <h3 class="font-black italic uppercase tracking-tighter text-3xl text-slate-800">Tiket Saya</h3>
            </div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.3em] ml-5">Riwayat Pengaduan Layanan Helpdesk</p>
        </div>

        @if (Auth::user()->role !== 'technician')

        <a href="/tickets/create" wire:navigate class="btn btn-primary rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl shadow-primary/30">

            <span class="font-black italic uppercase tracking-widest text-xs">+ Tambah Keluhan</span>
        </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-warning/10 text-warning flex items-center justify-center font-black italic">W</div>
            <div>
                <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Waiting</div>
                <div class="text-2xl font-black italic text-slate-800">{{ $myTickets->where('status', 'pending')->count() }}</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-info/10 text-info flex items-center justify-center font-black italic">P</div>
            <div>
                <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest">On Process</div>
                <div class="text-2xl font-black italic text-slate-800">{{ $myTickets->where('status', 'process')->count() }}</div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-success/10 text-success flex items-center justify-center font-black italic">R</div>
            <div>
                <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Resolved</div>
                <div class="text-2xl font-black italic text-slate-800">{{ $myTickets->where('status', 'closed')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden shadow-indigo-500/5">
        <div class="p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-50/30">
            <div class="relative w-full md:w-96 text-sm">
                <input type="text" wire:model.live="search" class="input input-bordered w-full rounded-2xl pl-12 h-12 bg-white focus:ring-4 ring-primary/5 transition-all border-slate-200" placeholder="Cari tiket...">
                <svg class="w-5 h-5 absolute left-4 top-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <div class="overflow-x-auto card bg-base-100 shadow-2xl rounded-[2.5rem] border border-base-content/5">
    <table class="table table-lg w-full border-separate border-spacing-y-3">
        <thead class="bg-base-200/50 text-base-content/40 uppercase text-[10px] font-black tracking-[0.2em]">
            <tr>
                <th class="pl-10 py-6 rounded-l-3xl">Ticket Information</th>
                <th>Status Tracking</th>
                <th class="text-right pr-10 rounded-r-3xl">Control Actions</th>
            </tr>
        </thead>

        <tbody class="text-base-content/80">
            @foreach($myTickets as $ticket)
            <tr class="group hover:bg-base-200/30 transition-all duration-300 shadow-sm shadow-base-content/5">
                <td class="pl-10 py-5 rounded-l-3xl border-none">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center font-black italic text-xs shadow-inner">
                            #{{ substr($ticket->ticket_number, -3) }}
                        </div>
                        <div>
                            <div class="font-black text-sm tracking-tight uppercase italic">{{ $ticket->ticket_number }}</div>
                            <div class="text-[9px] opacity-40 font-bold uppercase tracking-widest mt-0.5">
                                Created {{ $ticket->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </td>

                <td class="border-none">
                    @php
                        $statusColor = match($ticket->status) {
                            'pending' => 'badge-warning',
                            'process' => 'badge-info',
                            'closed'  => 'badge-success',
                            default   => 'badge-ghost',
                        };
                    @endphp
                    <div class="badge {{ $statusColor }} badge-outline font-black text-[9px] uppercase px-4 py-3 italic tracking-widest bg-white/50">
                        {{ $ticket->status }}
                    </div>
                </td>

                <td class="text-right pr-10 rounded-r-3xl border-none">
                    <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">

                        <a href="{{ route('tickets.details', $ticket->id) }}"
                           class="btn btn-ghost btn-sm bg-base-200/50 rounded-xl hover:bg-info hover:text-white transition-all shadow-sm">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>

                        @php
                            $canEdit = false;
                            if(Auth::id() == $ticket->user_id && $ticket->status == 'pending') $canEdit = true;
                            if(in_array(Auth::user()->role, ['admin', 'hrd']) && $ticket->status != 'closed') $canEdit = true;
                            if(Auth::id() == $ticket->technician_id && $ticket->status == 'process') $canEdit = true;
                        @endphp

                        @if (Auth::user()->role === 'staff' && $canEdit)
                            <a href="{{ route('tickets.edit', $ticket->id) }}"
                               class="btn btn-ghost btn-sm bg-base-200/50 rounded-xl hover:bg-warning hover:text-warning-content">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        @endif

                        @if($ticket->status == 'closed')
                            <a href="{{ route('tickets.print', $ticket->id) }}" target="_blank"
                               class="btn btn-ghost btn-sm bg-base-200/50 rounded-xl hover:bg-error hover:text-white group/print">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            </a>
                        @endif

                        @if(Auth::id() == $ticket->user_id && $ticket->status == 'pending')
                            <button wire:click="deleteTicket({{ $ticket->id }})"
                                    onclick="confirm('Batalkan tiket ini?') || event.stopImmediatePropagation()"
                                    class="btn btn-ghost btn-sm bg-base-200/50 rounded-xl hover:bg-error/20 hover:text-error">
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
        <div class="p-8 border-t border-slate-100 bg-slate-50/20">
            {{ $myTickets->links() }}
        </div>
    </div>
</div>
</div>

