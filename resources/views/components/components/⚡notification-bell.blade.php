<?php

use Livewire\Component;
use App\Models\TicketHistory;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function with()
    {
        $user = Auth::user();
        $query = TicketHistory::where('user_id', '!=', $user->id);

        // Filter berdasarkan Role (Staff/Teknisi)
        if ($user->role === 'staff') {
            $query->whereHas('ticket', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->role === 'technician') {
            $query->whereHas('ticket', fn($q) => $q->where('technician_id', $user->id));
        }

        return [
            'notifications' => $query->latest()->take(10)->get(),
            // Menghitung angka notifikasi yang belum dibaca
            'unreadCount' => $query->where('is_read', false)->count()
        ];
    }

    // Fungsi untuk merubah status false menjadi true (read)
    public function markAsRead($id)
    {
        $notification = TicketHistory::find($id);
        if ($notification) {
            $notification->update(['is_read' => true]);
        }

        // Opsional: Redirect ke detail tiket setelah klik
        return $this->redirectRoute('tickets.details', $notification->ticket_id, navigate: true);
    }

    public function markAllAsRead()
    {
        TicketHistory::where('is_read', false)->update(['is_read' => true]);
    }
};
?><div>
    <div class="dropdown dropdown-end" wire:poll.15s>
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

        <ul tabindex="0" class="dropdown-content z-[50] menu p-0 shadow-2xl bg-base-100 rounded-[2rem] w-80 border border-base-content/5 mt-4 overflow-hidden">
            <li class="bg-base-200/50 p-5 flex flex-row justify-between items-center border-b border-base-content/5">
                <span class="font-black italic uppercase text-xs tracking-widest text-primary">Notifications</span>
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="btn btn-xs btn-ghost text-[9px] font-black uppercase tracking-widest opacity-50">Mark all as read</button>
                @endif
            </li>

            <div class="max-h-96 overflow-y-auto">
                @forelse($notifications as $notif)
                    <li class="{{ !$notif->is_read ? 'bg-primary/5 border-l-4 border-primary' : '' }}">
                        <a wire:click.prevent="markAsRead({{ $notif->id }})"
                           class="flex flex-col items-start gap-1 py-4 px-6 active:bg-base-200">

                            <div class="flex justify-between w-full items-center">
                                <span class="font-black text-[10px] uppercase italic {{ !$notif->is_read ? 'text-primary' : 'opacity-40' }}">
                                    {{ $notif->ticket->ticket_number }}
                                </span>
                                <span class="text-[8px] font-bold opacity-30 uppercase">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>

                            {{-- <p class="text-[11px] {{ !$notif->is_read ? 'font-black' : 'font-medium opacity-60' }} leading-tight">
                                {{ $notif->user->name }} mengubah status ke <span class="italic text-primary">{{ $notif->status_to }}</span>
                            </p> --}}

                            @if($notif->comment)
                                <p class="text-[11px] {{ !$notif->is_read ? 'font-black' : 'font-medium opacity-60' }} leading-tight">
                                    "{{ $notif->comment }}"
                                </p>
                            @endif
                        </a>
                    </li>
                @empty
                    <li class="p-10 text-center flex flex-col items-center opacity-20">
                        <span class="text-[10px] font-black uppercase italic">No new messages</span>
                    </li>
                @endforelse
            </div>
        </ul>
    </div>
</div>
