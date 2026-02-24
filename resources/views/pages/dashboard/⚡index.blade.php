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
        $target_departement = $query->with('target_departement');

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

        // if ($user->role === 'technician') {
        //     $query->whereIn('status', ['process', 'closed'])
        //           ->where('technician_id', $user->id);
        // }

        $tickets = $query->latest()->take(5)->get();

        // Statistik Dinamis
        $stats = [
            'total'   => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'proses' => (clone $query)->where('status', 'proses')->count(),
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
<div class="max-w-7xl mx-auto p-6 lg:p-10 space-y-12">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="badge badge-primary badge-outline font-black text-[9px] uppercase tracking-[0.3em] mb-3 px-4 py-3">  Dashboard</div>
            <h5 class="text-4xl font-black text-slate-900 tracking-tight  uppercase leading-none">
                Halo, {{ explode(' ', $user->name)[0] }}! <span class="text-primary">.</span>
            </h5>
            <p class="text-slate-400 font-bold mt-2 uppercase tracking-[0.2em] text-[10px] flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Unit Monitoring • {{ now()->format('d F Y') }}
            </p>
        </div>

        <div class="flex items-center gap-4">
            @if (Auth::user()->role !== 'technician')
            <a href="{{ route('tickets.create') }}" wire:navigate class="btn btn-primary btn-md rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-2xl shadow-primary/40 group border-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                Buat Pengaduan
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="relative overflow-hidden bg-slate-900 p-8 rounded-[3rem] text-white shadow-2xl group">
            <div class="absolute right-0 top-0 w-40 h-40 bg-primary/20 blur-[80px] rounded-full"></div>
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Total Volume</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-7xl font-black mt-2  tracking-tighter">{{ $stats['total'] }}</h3>
                <span class="text-xs font-bold text-primary  uppercase">Tickets</span>
            </div>
            <div class="mt-8">
                <div class="w-full bg-white/10 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-primary h-full w-2/3 rounded-full"></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 flex flex-col justify-between group hover:-translate-y-2 transition-all duration-500">
            <div class="flex justify-between items-center">
                <div class="p-4 bg-amber-50 rounded-[1.5rem] text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Active</span>
            </div>
            <div class="mt-10 grid grid-cols-2 gap-8 divide-x divide-slate-200">
    <div class="text-left">
        <h3 class="text-5xl font-black text-slate-900 tracking-tighter">{{ $stats['pending'] }}</h3>
        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Pending Review</p>
    </div>
    <div class="pl-8 text-left">
        <h3 class="text-5xl font-black text-slate-900 tracking-tighter">{{ $stats['proses'] }}</h3>
        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">In Progress</p>
    </div>
</div>

        </div>

        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 flex flex-col justify-between group hover:-translate-y-2 transition-all duration-500 border-b-4 border-b-green-500">
            <div class="flex justify-between items-center">
                <div class="p-4 bg-green-50 rounded-[1.5rem] text-green-500 group-hover:bg-green-500 group-hover:text-white transition-all duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <span class="text-[9px] font-black text-green-500 uppercase tracking-widest">Completed</span>
            </div>
            <div class="mt-10">
                <h3 class="text-5xl font-black text-slate-900  tracking-tighter">{{ $stats['done'] }}</h3>
                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Resolved Issues</p>
            </div>
        </div>

        <div class="bg-primary p-8 rounded-[3rem] shadow-xl shadow-primary/20 flex flex-col justify-center items-center text-center relative overflow-hidden text-primary-content">
            <div class="absolute inset-0 opacity-20 scale-150 rotate-12">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <p class="text-primary-content/60 text-[9px] font-black uppercase tracking-[0.3em] mb-2 z-10">Identity</p>
            <div class="font-black  text-3xl uppercase tracking-tighter z-10">{{ $user->role }}</div>
            <div class="mt-4 py-1.5 px-4 bg-white/20 backdrop-blur-md rounded-full font-black uppercase text-[8px] tracking-[0.2em] z-10 border border-white/30">Verified Access</div>
        </div>
    </div>

    <div class="bg-white rounded-[3.5rem] shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden mt-12">
        <div class="p-12 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-end gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-1 bg-primary rounded-full"></div>
                    <span class="text-[10px] font-black text-primary uppercase tracking-[0.4em]">Recent Activity</span>
                </div>
                <h3 class="text-3xl font-black text-slate-900  tracking-tighter uppercase leading-none">Tiket Terbaru</h3>
            </div>
            <a href="{{ route('tickets.index') }}" wire:navigate class="btn btn-ghost btn-sm hover:bg-slate-50 rounded-xl text-slate-400 hover:text-primary font-black uppercase text-[9px] tracking-widest transition-all">
                Explore All <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto px-8 pb-8">
            <table class="table w-full border-separate border-spacing-y-4">
                <thead class="text-slate-400 font-black uppercase text-[8px] tracking-[0.4em]">
                    <tr>
                        <th class="bg-transparent border-none pl-6">Identifier</th>
                        <th class="bg-transparent border-none">Requester Info</th>
                        <th class="bg-transparent border-none">Category & Dept</th>
                        <th class="bg-transparent border-none text-center">Current Status</th>
                        <th class="bg-transparent border-none text-right pr-6">Management</th>
                    </tr>
                </thead>

                <tbody class="text-slate-600">
                    @forelse($tickets as $ticket)
                    {{-- PINDAHKAN LOGIKA WARNA KE SINI --}}
        @php
            $color = match($ticket->status) {
                'pending' => 'amber-500',
                'process' => 'blue-500',
                'closed'  => 'green-500',
                default   => 'slate-400'
            };

            // Variabel untuk background transparan (DaisyUI/Tailwind)
            $bgColor = match($ticket->status) {
                'pending' => 'bg-amber-500/10',
                'process' => 'bg-blue-500/10',
                'closed'  => 'bg-green-500/10',
                default   => 'bg-slate-500/10'
            };
        @endphp
                    <tr class="group">
                        <td class="bg-slate-50/50 border border-slate-100 group-hover:bg-white group-hover:border-primary/20 group-hover:shadow-xl group-hover:shadow-slate-200/40 rounded-l-[2.5rem] pl-8 transition-all duration-500">
                            <div class="flex flex-col">
                                <span class="font-black text-primary uppercase text-sm tracking-tighter">
                                    #{{ $ticket->ticket_number }}
                                </span>
                                <span class="text-[9px] opacity-40 font-bold uppercase mt-1 tracking-tighter">
                                    {{ $ticket->aset->nama_aset ?? 'Generic Request' }}
                                </span>
                            </div>
                        </td>

                        <td class="bg-slate-50/50 border-t border-b border-slate-100 group-hover:bg-white group-hover:border-primary/20 group-hover:shadow-xl group-hover:shadow-slate-200/40 transition-all duration-500">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-slate-200 flex items-center justify-center font-black text-slate-500 text-xs group-hover:bg-primary group-hover:text-white transition-all">
                                    {{ substr($ticket->user->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800 tracking-tight">{{ $ticket->user->name }}</span>
                                    <span class="text-[9px] opacity-40 font-bold ">{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="bg-slate-50/50 border-t border-b border-slate-100 group-hover:bg-white group-hover:border-primary/20 group-hover:shadow-xl group-hover:shadow-slate-200/40 transition-all duration-500">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $ticket->category }}</span>
                                <span class="text-[9px] opacity-40 font-bold ">{{ $ticket->target_departement->name ?? 'General' }}</span>
                            </div>
                        </td>

                        <td class="bg-slate-50/50 border-t border-b border-slate-100 group-hover:bg-white group-hover:border-primary/20 group-hover:shadow-xl group-hover:shadow-slate-200/40 text-center transition-all duration-500">
                            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white border border-slate-100 shadow-sm group-hover:border-{{ $color }}/30 transition-all">
                                <span class="w-2 h-2 rounded-full bg-{{ $color }} animate-pulse"></span>
                                <span class="text-[9px] font-black uppercase tracking-[0.2em]  text-{{ $color }}">{{ $ticket->status }}</span>
                            </div>
                        </td>

                        <td class="bg-slate-50/50 border border-slate-100 group-hover:bg-white group-hover:border-primary/20 group-hover:shadow-xl group-hover:shadow-slate-200/40 text-right pr-8 rounded-r-[2.5rem] transition-all duration-500">
                            <a href="{{ route('tickets.details', $ticket->id) }}" wire:navigate
                               class="btn btn-circle btn-md border-none bg-white shadow-sm hover:bg-primary hover:text-white transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
