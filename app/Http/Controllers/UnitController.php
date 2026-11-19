<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    private function landlordId(): int
    {
        return (int) Landlord::where('user_id', Auth::id())
            ->value('id') ?: abort(403, 'Landlord profile not found.');
    }

    private function ownedUnit(int $unitId): Unit
    {
        return Unit::whereHas('property', function ($query) {
            $query->where('landlord_id', $this->landlordId());
        })->findOrFail($unitId);
    }

    public function edit(Unit $unit)
    {
        $unit = $this->ownedUnit($unit->id);
        $property = $unit->property;

        return view('units.edit', compact('unit', 'property'));
    }

    public function update(Request $request, Unit $unit)
    {
        $unit = $this->ownedUnit($unit->id);

        $validated = $request->validate([
            'unit_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'unit_number')
                    ->where('property_id', $unit->property_id)
                    ->ignore($unit->id),
            ],
            'unit_type' => ['nullable', 'string', 'max:50'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'garbage_fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                'vacant',
                'occupied',
                'maintenance',
            ])],
            'description' => ['nullable', 'string'],
        ]);

        $unit->update($validated);

        return redirect()
            ->route('properties.show', $unit->property_id)
            ->with('success', 'Unit updated successfully.');
    }
}
