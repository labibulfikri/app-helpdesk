<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketPrintController extends Controller
{
    public function print($id)
    {
        $ticket = Ticket::with(['user', 'technician', 'target_departement'])->findOrFail($id);

        // Cek jika status bukan closed, tidak boleh cetak
        if($ticket->status !== 'closed') {
            return back()->with('error', 'Tiket harus berstatus CLOSED untuk dicetak.');
        }

        $pdf = Pdf::loadView('pdf.ticket_ppp', compact('ticket'))->setPaper('a4', 'portrait');
        return $pdf->stream('PPP-'.$ticket->ticket_number.'.pdf');
    }
}
