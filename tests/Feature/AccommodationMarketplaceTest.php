<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Landlord;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccommodationMarketplaceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_accommodation_can_receive_booking_requests_and_track_views(): void
    {
        $landlord = $this->createLandlord();
        $stay = Accommodation::create([
            'landlord_id' => $landlord->id,
            'title' => 'Garden guest suite',
            'place_name' => 'Test Town',
            'price_per_night' => 7000,
            'max_guests' => 3,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('accommodations.public.show', $stay))
            ->assertOk()
            ->assertSee('Garden guest suite');
        $this->assertSame(1, $stay->fresh()->clicks_count);

        $this->post(route('accommodations.public.book', $stay), [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.test',
            'guest_phone' => '0712345678',
            'check_in' => now()->addDays(3)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guests' => 2,
            'guest_message' => 'Please confirm these dates.',
        ])->assertRedirect();

        $this->assertDatabaseHas('accommodation_bookings', [
            'accommodation_id' => $stay->id,
            'guest_email' => 'guest@example.test',
            'status' => 'pending',
        ]);
    }

    public function test_confirmed_booking_dates_cannot_be_requested_again(): void
    {
        $landlord = $this->createLandlord();
        $stay = Accommodation::create([
            'landlord_id' => $landlord->id,
            'title' => 'Booked suite',
            'place_name' => 'Test Town',
            'price_per_night' => 7000,
            'max_guests' => 3,
            'status' => 'published',
        ]);
        AccommodationBooking::create([
            'accommodation_id' => $stay->id,
            'guest_name' => 'First Guest',
            'guest_email' => 'first@example.test',
            'guest_phone' => '0712345678',
            'check_in' => now()->addDays(3)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guests' => 2,
            'status' => 'confirmed',
        ]);

        $this->from(route('accommodations.public.show', $stay))
            ->post(route('accommodations.public.book', $stay), [
                'guest_name' => 'Second Guest',
                'guest_email' => 'second@example.test',
                'guest_phone' => '0712345678',
                'check_in' => now()->addDays(4)->toDateString(),
                'check_out' => now()->addDays(6)->toDateString(),
                'guests' => 1,
            ])
            ->assertSessionHasErrors('check_in');
    }

    private function createLandlord(): Landlord
    {
        $user = User::create([
            'name' => 'Accommodation host',
            'email' => Str::uuid().'@example.test',
            'password' => 'password',
            'role' => 'landlord',
        ]);

        return Landlord::create([
            'user_id' => $user->id,
            'business_name' => 'Stay host',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }
}
