<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TicketExport; // Kita akan buat file ini

class ReportExportController extends Controller
{
    public function exportPdf(Request $request) {
    $tickets = Ticket::with('aset', 'technician')
        ->whereBetween('created_at', [$request->startDate.' 00:00:00', $request->endDate.' 23:59:59'])
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.ticket-pdf', [
        'tickets' => $tickets,
        'startDate' => $request->startDate,
        'endDate' => $request->endDate
    ])->setPaper('a4', 'landscape');

    return $pdf->download('Masterlist_Maintenance.pdf');
}

    public function exportExcel(Request $request)
    {
        return Excel::download(new TicketExport($this->getQuery($request)), 'Masterlist-PPP.xlsx');
    }

    protected function getQuery(Request $request)
    {
        $query = Ticket::with(['aset', 'technician'])
            ->whereBetween('created_at', [$request->startDate . ' 00:00:00', $request->endDate . ' 23:59:59']);

        if ($request->kode_ppp) $query->where('kode_ppp', $request->kode_ppp);
        if ($request->technicianFilter) $query->where('technician_id', $request->technicianFilter);
        if ($request->statusFilter) $query->where('status', $request->statusFilter);

        return $query;
    }
}

?>
