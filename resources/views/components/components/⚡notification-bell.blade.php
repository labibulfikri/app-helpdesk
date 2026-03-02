<?php

use Livewire\Component;
use App\Models\TicketHistory;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function with()
    {
        $userId = Auth::id();

        // Query utama: Ambil history yang ditujukan khusus untuk user ini (received_id)
        // Dan pastikan bukan user itu sendiri yang melakukan aksi (opsional)
        $query = TicketHistory::where('received_id', $userId)
                               ->with('ticket'); // Eager loading agar tidak berat

        return [
            'notifications' => $query->latest()->take(10)->get(),
            // Hitung angka notifikasi yang belum dibaca khusus untuk user ini
            'unreadCount'   => TicketHistory::where('received_id', $userId)
                                           ->where('is_read', false)
                                           ->count()
        ];
    }
public function markAsRead($id)
{
    $notification = TicketHistory::where('id', $id)
                                 ->where('received_id', Auth::id())
                                 ->first();

    if ($notification) {
        $notification->update(['is_read' => true]);
        // Pastikan route 'tickets.details' menerima parameter ID ticket
        return $this->redirectRoute('tickets.details', ['id' => $notification->ticket_id], navigate: true);
    }
}

    public function markAllAsRead()
    {
        // Hanya tandai 'read' untuk notifikasi yang ditujukan ke saya
        TicketHistory::where('received_id', Auth::id())
                     ->where('is_read', false)
                     ->update(['is_read' => true]);
    }
};
?>
<div>
    <div class="dropdown dropdown-end" wire:poll.30s> {{-- Interval diperpanjang sedikit agar tidak berat --}}
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
            <div class="indicator">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>

                @if($unreadCount > 0)
                    <span class="indicator-item badge badge-error badge-sm font-black text-[10px] text-white animate-bounce">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </div>
        </div>

        <ul tabindex="0" class="dropdown-content z-[100] menu p-0 shadow-2xl bg-base-100 rounded-[2rem] w-80 border border-base-content/5 mt-4 overflow-hidden">
            <li class="bg-base-200/50 p-5 flex flex-row justify-between items-center border-b border-base-content/5">
                <span class="font-black italic uppercase text-xs tracking-widest text-primary">Notifications</span>
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="btn btn-xs btn-ghost text-[9px] font-black uppercase tracking-widest opacity-50 hover:text-primary">Mark all as read</button>
                @endif
            </li>

            <div class="max-h-96 overflow-y-auto scrollbar-hide">
                @forelse($notifications as $notif)
                    <li wire:key="notif-{{ $notif->id }}" class="border-b border-base-content/5 last:border-0 {{ !$notif->is_read ? 'bg-primary/5' : '' }}">
        <a wire:click.prevent="markAsRead({{ $notif->id }})" href="#"
                           class="flex flex-col items-start gap-1 py-4 px-6 active:bg-base-200 transition-all duration-200">

                            <div class="flex justify-between w-full items-center mb-1">
                                <span class="font-black text-[10px] uppercase italic {{ !$notif->is_read ? 'text-primary' : 'opacity-40' }}">
                                    {{ $notif->ticket->ticket_number ?? 'No Ticket' }}
                                </span>
                                <span class="text-[8px] font-bold opacity-30 uppercase">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>

                            @if($notif->comment)
                                <p class="text-[11px] {{ !$notif->is_read ? 'font-black text-base-content' : 'font-medium opacity-60 text-base-content/70' }} leading-tight">
                                    {{ Str::limit($notif->comment, 80) }}
                                </p>
                            @endif

                            {{-- Badge status opsional --}}
                            <span class="text-[7px] font-black uppercase tracking-tighter opacity-30 mt-1">
                                Action by: {{ $notif->user->name ?? 'System' }}
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="p-12 text-center flex flex-col items-center gap-2 opacity-20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <span class="text-[10px] font-black uppercase italic">Inbox is empty</span>
                    </li>
                @endforelse
            </div>
        </ul>
    </div>
</div>
