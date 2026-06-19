<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use App\Models\Message;
use App\Models\MessageThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $threads = MessageThread::where(function ($q) use ($userId) {
            $q->whereHas('booking', fn ($b) => $b->where('user_id', $userId))
                ->orWhere('user_id', $userId);
        })
            ->with([
                'booking.room.kost:id,name,slug,thumbnail',
                'booking.room.kost.tenant.userProfile:user_id,name,avatar',
                'kost:id,name,slug,thumbnail',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest()
            ->get();

        return Inertia::render('User/Messages/Index', ['threads' => $threads]);
    }

    public function show(Request $request, MessageThread $thread): Response
    {
        $this->authorize('view', $thread);

        $thread->load([
            'booking.room.kost:id,name,slug',
            'booking.room.kost.tenant.userProfile:user_id,name,avatar',
            'kost:id,name,slug',
            'kost.tenant.userProfile:user_id,name,avatar',
            'messages.sender.userProfile:user_id,name,avatar',
        ]);

        Message::where('message_thread_id', $thread->id)
            ->where('user_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('User/Messages/Show', [
            'thread' => $thread,
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function store(Request $request, MessageThread $thread): RedirectResponse
    {
        $this->authorize('create', $thread);

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        Message::create([
            'message_thread_id' => $thread->id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        return back();
    }

    public function storeFromKost(Request $request, Kost $kost): RedirectResponse
    {
        $user = $request->user();

        $thread = MessageThread::firstOrCreate(
            ['kost_id' => $kost->id, 'user_id' => $user->id],
        );

        return redirect()->route('dashboard.messages.show', $thread);
    }
}
