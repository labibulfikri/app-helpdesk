<?php


use Livewire\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function render()
    {
        $user = Auth::user();
        $query = Ticket::query();
        $asset = $query->with('aset');

        // // Logika Role: Jika bukan admin/hrd, hanya tampilkan milik sendiri
        // if (!in_array($user->role, ['admin', 'hrd'])) {
        //     $query->where('user_id', $user->id);
        // }

        // // jika teknisi tampilkan tiket dengan status process dan done yang diberikan kedirinya
        // if ($user->role === 'technician') {
        //     $query->whereIn('status', ['process', 'closed'])
        //           ->where('technician_id', $user->id);
        // }

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

        // if ($user->role === 'technician') {
        //     $query->whereIn('status', ['process', 'closed'])
        //           ->where('technician_id', $user->id);
        // }

        $tickets = $query->latest()->take(5)->get();

        // Statistik Dinamis
        $stats = [
            'total'   => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'process')->count(),
            'done'    => (clone $query)->where('status', 'closed')->count(),
        ];

        return view('pages.dashboard.⚡index', [
            'tickets' => $tickets,
            'stats'   => $stats,
            'user'    => $user
        ])->layout('layouts.app');
    }
};
?>
<div class="max-w-7xl mx-auto p-4 lg:p-10 space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-4xl font-black text-slate-900 tracking-tight italic uppercase">
                Halo, {{ explode(' ', $user->name)[0] }}! 👋
            </h2>
            <p class="text-slate-500 font-bold mt-1 uppercase tracking-[0.2em] text-[10px] flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                Ringkasan Helpdesk SBE • {{ now()->format('d M Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            @if(in_array($user->role, ['admin', 'hrd']))
                <button class="btn btn-outline border-2 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Report
                </button>

                @endif
            @if (Auth::user()->role !== 'technician')
            <a href="{{ route('tickets.create') }}" wire:navigate class="btn btn-primary rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl shadow-primary/30">
                + Pengaduhan Baru
            </a>
            @endif

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="relative overflow-hidden bg-primary p-8 rounded-[2.5rem] text-primary-content shadow-2xl shadow-primary/40 group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80">Total Tiket</p>
            <h3 class="text-6xl font-black mt-2 italic">{{ $stats['total'] }}</h3>
            <div class="mt-6 flex items-center gap-2">
                <span class="py-1 px-3 bg-white/20 rounded-full text-[9px] font-black uppercase tracking-widest italic">Periode Ini</span>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-xl transition-all border-b-4 border-b-amber-400">
            <div class="flex justify-between items-start">
                <div class="p-4 bg-amber-50 rounded-2xl text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-black text-slate-900 italic">{{ $stats['pending'] }}</h3>
                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Pending Tiket</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-xl transition-all border-b-4 border-b-green-400">
            <div class="flex justify-between items-start">
                <div class="p-4 bg-green-50 rounded-2xl text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-black text-slate-900 italic">{{ $stats['done'] }}</h3>
                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Selesai</p>
            </div>
        </div>

        <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-2xl flex flex-col justify-center items-center text-center relative overflow-hidden group">
            <div class="absolute inset-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <p class="text-slate-500 text-[9px] font-black uppercase tracking-[0.3em] mb-3 z-10">Access Level</p>
            <div class="text-white font-black italic text-2xl uppercase tracking-tighter z-10">{{ $user->role }}</div>
            <div class="badge badge-primary mt-4 py-3 px-6 rounded-xl font-black uppercase text-[8px] tracking-widest z-10">SBE System</div>
        </div>
    </div>

    <div class="bg-white rounded-[3rem] shadow-xl border border-slate-200 overflow-hidden transition-all hover:shadow-2xl">
        <div class="p-10 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-2xl font-black text-slate-900 italic tracking-tighter uppercase">Tiket Terbaru</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktivitas helpdesk 5 tiket terakhir</p>
            </div>
            <a href="{{ route('tickets.index') }}" wire:navigate class="btn btn-ghost hover:bg-slate-50 rounded-2xl text-primary font-black uppercase text-[10px] tracking-widest">
                Lihat Semua Tiket →
            </a>
        </div>

        <div class="overflow-x-auto w-full px-2">
    <table class="table w-full border-separate border-spacing-y-4">
        <thead class="text-base-content/30 font-black uppercase text-[9px] tracking-[0.3em]">
            <tr>
                <th class="bg-transparent border-none pl-6">Reference</th>
                <th class="bg-transparent border-none">Requester</th>
                <th class="bg-transparent border-none">Category</th>
                <th class="bg-transparent border-none text-center">Status</th>
                <th class="bg-transparent border-none text-right pr-6">Action</th>
            </tr>
        </thead>

        <tbody class="text-base-content/80">
            @forelse($tickets as $ticket)
            <tr class="group transition-all duration-300">
                <td class="bg-base-100 border border-base-content/5 group-hover:border-primary/30 rounded-l-[2rem] pl-6 transition-all">
                    <div class="flex flex-col">
                        <span class="font-black text-primary italic uppercase text-xs tracking-tighter">
                            #{{ $ticket->ticket_number }}
                        </span>
                        <span class="text-[8px] opacity-30 font-bold uppercase tracking-widest mt-0.5">
                            {{ $ticket->aset->nama_aset ?? 'No Asset' }}
                        </span>
                    </div>
                </td>

                <td class="bg-base-100 border-t border-b border-base-content/5 group-hover:border-primary/30 transition-all">
                    <div class="flex items-center gap-4">
                        {{-- <div class="avatar placeholder ">
                            <div class="bg-primary/5 text-primary rounded-2xl w-11 h-11 border border-primary/10 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                                <span class="text-center font-black text-sm">{{ substr($ticket->user->name, 0, 1) }}</span>
                            </div>
                        </div> --}}
                        <div class="flex flex-col">
                            <span class="text-sm font-black tracking-tight tracking-tight">{{ $ticket->user->name }}</span>
                            <span class="text-[9px] opacity-40 font-medium  italic">{{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </td>

                <td class="bg-base-100 border-t border-b border-base-content/5 group-hover:border-primary/30 transition-all">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ $ticket->category }}</span>
                        <span class="text-[9px] opacity-30 font-bold">{{ $ticket->unit_number ?? 'General' }}</span>
                    </div>
                </td>

                <td class="bg-base-100 border-t border-b border-base-content/5 group-hover:border-primary/30 text-center transition-all">
                    @php
                        $color = match($ticket->status) {
                            'pending' => 'warning',
                            'process' => 'primary',
                            'closed'  => 'success',
                            default   => 'ghost'
                        };
                    @endphp
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-{{ $color }}/10 text-{{ $color }} border border-{{ $color }}/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }} animate-pulse"></span>
                        <span class="text-[9px] font-black uppercase tracking-[0.1em] italic">{{ $ticket->status }}</span>
                    </div>
                </td>

                <td class="bg-base-100 border border-base-content/5 group-hover:border-primary/30 text-right pr-6 rounded-r-[2rem] transition-all">
                    <a href="{{ route('tickets.details', $ticket->id) }}" wire:navigate
                       class="btn btn-circle btn-sm bg-base-200 border-none hover:bg-primary hover:text-white transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-32 text-center">
                    <div class="flex flex-col items-center opacity-10">
                        <div class="p-8 rounded-[3rem] border-4 border-dashed border-base-content mb-4">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <p class="font-black italic uppercase tracking-[0.5em] text-xs">Zero Tickets Found</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
    </div>
</div>
