<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicAccommodationController extends Controller
{
    public function show(Accommodation $accommodation)
    {
        abort_unless($accommodation->status === 'published', 404);
        $accommodation->increment('clicks_count');
        $accommodation->load('images');

        return view('accommodations.show', compact('accommodation'));
    }

    public function book(Request $request, Accommodation $accommodation)
    {
        abort_unless($accommodation->status === 'published', 404);
        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:150'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:30'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'between:1,'.$accommodation->max_guests],
            'guest_message' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($accommodation, $validated) {
            $locked = Accommodation::whereKey($accommodation->id)
                ->where('status', 'published')
                ->lockForUpdate()
                ->firstOrFail();
            $conflict = AccommodationBooking::where('accommodation_id', $locked->id)
                ->where('status', 'confirmed')
                ->where('check_in', '<', $validated['check_out'])
                ->where('check_out', '>', $validated['check_in'])
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'check_in' => 'Those dates are already booked. Please choose another date range.',
                ]);
            }

            AccommodationBooking::create([
                'accommodation_id' => $locked->id,
                ...$validated,
                'status' => 'pending',
            ]);
        });

        return back()->with(
            'success',
            'Booking request sent. The accommodation host will contact you to confirm availability.'
        );
    }
}
