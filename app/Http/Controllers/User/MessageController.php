<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        $threads = MessageThread::whereHas('booking', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with([
                'booking.room.kost:id,name,slug,thumbnail',
                'booking.room.kost.tenant.userProfile:user_id,name,avatar',
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
}
