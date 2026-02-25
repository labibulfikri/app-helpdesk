<?php

use Livewire\Component;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Departement; // Pastikan Model ini ada
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $statusFilter = '';
    public $technicianFilter = '';
    public $deptFilter = '';

    public function mount()
    {
        // Default filter bulan berjalan
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updated()
    {
        $this->resetPage();
        // Mengirimkan event ke JS untuk update grafik
        $this->dispatch('update-chart-data', data: $this->getChartData());
    }

    protected function getChartData()
    {
        // Base query untuk chart agar mengikuti filter yang aktif
        $baseQuery = Ticket::whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        if ($this->technicianFilter) {
            $baseQuery->where('technician_id', $this->technicianFilter);
        }

        if ($this->deptFilter) {
            $baseQuery->where('target_departement_id', $this->deptFilter);
        }

        if ($this->statusFilter) {
            $baseQuery->where('status', $this->statusFilter);
        }

        return [
            (clone $baseQuery)->where('status', 'pending')->count(),
            (clone $baseQuery)->where('status', 'proses')->count(),
            (clone $baseQuery)->where('status', 'done')->count(),
            (clone $baseQuery)->where('status', 'rejected')->count(),
            (clone $baseQuery)->where('status', 'closed')->count(),
        ];
    }

    public function render()
    {
        $query = Ticket::with(['user', 'technician', 'aset', 'target_departement'])
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        // Filter Tabel
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->technicianFilter) $query->where('technician_id', $this->technicianFilter);
        if ($this->deptFilter) $query->where('target_departement_id', $this->deptFilter);

        return view('pages.report.⚡index', [
            'tickets' => $query->latest()->paginate(15),
            'technicians' => User::where('role', 'technician')->get(),
            'departments' => Departement::all(),
            'chartValues' => $this->getChartData(),
            'totalStats' => (clone $query)->count(),
            'doneStats' => (clone $query)->where('status', 'done')->count(),
        ])->layout('layouts.app');
    }
}
?><div class="min-h-screen bg-base-200/50 p-4 lg:p-10">
    <style>
        @media print {
            .no-print, .btn, .select, .input, .pagination, .navbar, .sidebar { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .card { border: 1px solid #eee !important; box-shadow: none !important; border-radius: 0 !important; }
            .max-w-7xl { max-width: 100% !important; width: 100% !important; }
            canvas { max-height: 250px !important; width: 100% !important; }
        }
    </style>

    <div class="max-w-7xl mx-auto space-y-6">
        {{-- HEADER --}}
        <div class="flex justify-between items-end no-print">
            <div>
                <h1 class="text-4xl font-black italic uppercase tracking-tighter">Report Analytics</h1>
                <p class="text-[10px] font-bold opacity-50 uppercase tracking-[0.2em]">Maintenance & Dept Performance</p>
            </div>
            {{-- <button onclick="window.print()" class="btn btn-primary rounded-2xl font-black uppercase px-8 shadow-xl shadow-primary/20">
                Print Report
            </button> --}}
        </div>

        {{-- FILTER BOX --}}
        <div class="card bg-white shadow-sm border border-base-300 rounded-[2.5rem] no-print">
            <div class="p-8 grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="form-control">
                    <label class="label font-black text-[10px] uppercase opacity-40">Dari Tanggal</label>
                    <input type="date" wire:model.live="startDate" class="input input-bordered rounded-2xl font-bold text-xs">
                </div>
                <div class="form-control">
                    <label class="label font-black text-[10px] uppercase opacity-40">Sampai Tanggal</label>
                    <input type="date" wire:model.live="endDate" class="input input-bordered rounded-2xl font-bold text-xs">
                </div>
                <div class="form-control">
                    <label class="label font-black text-[10px] uppercase opacity-40">Departemen</label>
                    <select wire:model.live="deptFilter" class="select select-bordered rounded-2xl font-bold uppercase text-xs">
                        <option value="">Semua Dept</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label font-black text-[10px] uppercase opacity-40">Teknisi</label>
                    <select wire:model.live="technicianFilter" class="select select-bordered rounded-2xl font-bold uppercase text-xs">
                        <option value="">Semua Teknisi</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label font-black text-[10px] uppercase opacity-40">Status</label>
                    <select wire:model.live="statusFilter" class="select select-bordered rounded-2xl font-bold uppercase text-xs">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="proses">Proses</option>
                        <option value="done">Done</option>
                        <option value="rejected">Rejected</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- DASHBOARD CONTENT --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- CHART CARD --}}
            <div class="lg:col-span-8 card bg-white p-8 shadow-sm border border-base-300 rounded-[3rem] relative overflow-hidden">
                {{-- LOADING SPINNER --}}
                <div wire:loading wire:target="startDate, endDate, statusFilter, technicianFilter, deptFilter" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                </div>

                <h3 class="text-xs font-black uppercase italic opacity-40 mb-6 tracking-widest">Status Distribution</h3>
                <div class="h-72 w-full">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>

            {{-- SUMMARY STATS --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-primary text-primary-content p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden">
                    <p class="text-[10px] font-black uppercase opacity-60">Total Terfilter</p>
                    <h2 class="text-5xl font-black italic mt-2">{{ $totalStats }}</h2>
                    <div class="absolute -right-4 -bottom-4 text-8xl opacity-10 font-black italic uppercase text-white">Data</div>
                </div>
                <div class="bg-white border border-base-300 p-8 rounded-[2.5rem]">
                    <p class="text-[10px] font-black uppercase opacity-40 italic font-bold">Selesai (Done)</p>
                    <h2 class="text-4xl font-black text-success mt-1 italic leading-none">{{ $doneStats }}</h2>
                </div>
                <div class="bg-white border border-base-300 p-8 rounded-[2.5rem]">
                    <p class="text-[10px] font-black uppercase opacity-40 italic font-bold">Completion Rate</p>
                    <h2 class="text-4xl font-black text-info mt-1 italic leading-none">
                        {{ $totalStats > 0 ? round(($doneStats / $totalStats) * 100) : 0 }}%
                    </h2>
                </div>
            </div>
        </div>

        {{-- TABLE DATA --}}
        <div class="card bg-white shadow-sm border border-base-300 rounded-[2.5rem] overflow-hidden">
            <div class="overflow-x-auto p-2">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-100 text-[10px] font-black uppercase opacity-50 border-b border-base-300 text-center">
                            <th class="p-6 text-left">No Tiket</th>
                            <th>Dept</th>
                            <th>Aset</th>
                            <th>Teknisi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs font-bold uppercase">
                        @foreach($tickets as $t)
                        <tr class="hover:bg-base-200/50 text-center">
                            <td class="p-6 text-left font-black italic text-primary tracking-tighter">{{ $t->ticket_number }}</td>
                            <td class="opacity-60">{{ $t->target_departement->name ?? '-' }}</td>
                            <td>{{ $t->aset->nama_aset ?? '-' }}</td>
                            <td>{{ $t->technician->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-sm font-black text-[9px] px-4
                                    {{ $t->status == 'done' ? 'badge-success text-white' : ($t->status == 'rejected' ? 'badge-error text-white' : 'badge-ghost border-base-300') }}">
                                    {{ $t->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-8 no-print border-t border-base-100">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT AREA --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let myChart;

    function initChart(dataValues) {
        const ctx = document.getElementById('reportChart');
        if (!ctx) return;

        if (myChart) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Proses', 'Done', 'Rejected', 'Closed'],
                datasets: [{
                    label: 'Total Tiket',
                    data: dataValues,
                    backgroundColor: ['#fbbd23', '#3b82f6', '#22c55e', '#ef4444', '#6b7280'],
                    borderRadius: 12,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600 },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { font: { weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                }
            }
        });
    }

    // Listener Livewire 3
    document.addEventListener('livewire:init', () => {
        // Init awal
        initChart(@json($chartValues));

        // Listen update dari PHP
        Livewire.on('update-chart-data', (event) => {
            // Livewire 3 mengirimkan data dalam properti 'data'
            initChart(event.data);
        });
    });

    // Support wire:navigate
    document.addEventListener('livewire:navigated', () => {
        initChart(@json($chartValues));
    });
</script>
