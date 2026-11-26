<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\SupportConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function show()
    {
        $landlordId = $this->landlordId();
        $conversation = SupportConversation::firstOrCreate(['landlord_id' => $landlordId]);
        $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $messages = $conversation->messages()->with('sender')->oldest()->get();

        return view('support.show', compact('conversation', 'messages'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);
        $conversation = SupportConversation::firstOrCreate([
            'landlord_id' => $this->landlordId(),
        ]);
        $conversation->messages()->create([
            'sender_user_id' => Auth::id(),
            'sender_type' => 'landlord',
            'message' => trim($validated['message']),
        ]);

        return back()->with('success', 'Message sent to technical support.');
    }

    private function landlordId(): int
    {
        $id = Landlord::where('user_id', Auth::id())->value('id');
        abort_unless($id, 403, 'Landlord account not found.');

        return (int) $id;
    }
}
