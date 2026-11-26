<?php

namespace Tests\Feature;

use App\Models\Landlord;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupportMessagingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landlord_can_open_support_and_mark_admin_replies_as_read(): void
    {
        $user = User::create([
            'name' => 'Support test landlord',
            'email' => Str::uuid().'@example.test',
            'password' => 'password',
            'role' => 'landlord',
        ]);
        $landlord = Landlord::create([
            'user_id' => $user->id,
            'business_name' => 'Support test landlord',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $conversation = SupportConversation::create(['landlord_id' => $landlord->id]);
        $reply = SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $user->id,
            'sender_type' => 'admin',
            'message' => 'How can we help?',
        ]);

        $this->actingAs($user)
            ->get(route('support.show'))
            ->assertOk()
            ->assertSee('How can we help?');

        $this->assertNotNull($reply->fresh()->read_at);
    }
}
