<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminInboxController extends Controller
{
    public function contacts()
    {
        $messages = ContactMessage::with('handler')->latest()->paginate(25);

        return view('admin.inbox.contacts', compact('messages'));
    }

    public function updateContact(Request $request, ContactMessage $message)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,resolved'],
        ]);
        $message->update([
            'status' => $validated['status'],
            'handled_by' => Auth::id(),
            'handled_at' => $validated['status'] === 'resolved' ? now() : $message->handled_at,
        ]);

        return back()->with('success', 'Contact message status updated.');
    }

    public function conversations()
    {
        $conversations = SupportConversation::with([
            'landlord.user',
            'messages' => fn ($query) => $query->latest()->limit(1),
        ])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->where('sender_type', 'landlord')
                    ->whereNull('read_at'),
            ])
            ->latest('updated_at')
            ->paginate(25);

        return view('admin.inbox.support', compact('conversations'));
    }

    public function showConversation(SupportConversation $conversation)
    {
        $conversation->messages()
            ->where('sender_type', 'landlord')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $conversation->load(['landlord.user', 'messages.sender']);
        $messages = $conversation->messages;

        return view('admin.inbox.conversation', compact('conversation', 'messages'));
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);
        $conversation->messages()->create([
            'sender_user_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => trim($validated['message']),
        ]);

        return back()->with('success', 'Support reply sent.');
    }
}
