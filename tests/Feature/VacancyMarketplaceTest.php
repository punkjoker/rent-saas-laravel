<?php

namespace Tests\Feature;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\VacancyListing;
use App\Services\VacancyMarketplace;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class VacancyMarketplaceTest extends TestCase
{
    use DatabaseTransactions;

    private Landlord $landlord;

    private Property $property;

    private string $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->town = 'Marketplace'.str_replace('-', '', (string) Str::uuid());
        $user = User::create([
            'name' => 'Vacancy test landlord',
            'email' => Str::uuid().'@example.test',
            'password' => 'password',
            'role' => 'landlord',
        ]);
        $this->landlord = Landlord::create([
            'user_id' => $user->id,
            'business_name' => 'Vacancy test business',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $this->property = Property::create([
            'landlord_id' => $this->landlord->id,
            'property_name' => 'Greenview '.$this->town,
            'property_type' => 'apartment',
            'town' => $this->town,
            'county' => $this->town.' County',
            'is_active' => true,
        ]);
    }

    public function test_landlord_vacancies_are_grouped_by_property_and_unit_type(): void
    {
        foreach ([['Bedsitter', 1000], ['bedsitter', 1500]] as $index => [$type, $rent]) {
            $unit = $this->createVacantUnit('A'.$index, $type, $rent);
            $this->publish($unit);
        }

        $cards = app(VacancyMarketplace::class)->cards($this->town);

        $this->assertCount(1, $cards);
        $this->assertSame(2, $cards->first()['count']);
        $this->assertSame(1000.0, $cards->first()['rent_min']);
        $this->assertSame(1500.0, $cards->first()['rent_max']);
        $listing = VacancyListing::where('property_id', $this->property->id)->firstOrFail();
        $this->assertSame(2, app(VacancyMarketplace::class)
            ->detail($listing->id)['availableUnits']);
        $this->assertCount(0, app(VacancyMarketplace::class)->cards('No'.$this->town));
    }

    public function test_vacancies_with_an_active_or_upcoming_tenancy_are_hidden(): void
    {
        $unit = $this->createVacantUnit('B1', 'One bedroom', 12000);
        $this->publish($unit);
        $tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'first_name' => 'Test',
            'last_name' => 'Tenant',
            'phone' => '0700000000',
        ]);
        Tenancy::create([
            'landlord_id' => $this->landlord->id,
            'tenant_id' => $tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $unit->id,
            'monthly_rent' => 12000,
            'deposit_amount' => 12000,
            'garbage_fee' => 0,
            'start_date' => now()->toDateString(),
            'lease_end_date' => now()->addYear()->toDateString(),
            'rent_due_day' => 1,
            'status' => 'upcoming',
        ]);

        $this->assertCount(0, app(VacancyMarketplace::class)->cards($this->town));
    }

    public function test_admin_vacancy_is_filterable_and_has_a_public_detail_page(): void
    {
        $listing = VacancyListing::create([
            'source' => 'admin',
            'title' => 'Test available bedsitter',
            'place_name' => $this->town,
            'unit_type' => 'Bedsitter',
            'monthly_rent' => 8500,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('home', ['place' => $this->town]))
            ->assertOk()
            ->assertSee('Test available bedsitter')
            ->assertSee('1 vacancy');

        $this->get(route('vacancies.show', $listing))
            ->assertOk()
            ->assertSee('Test available bedsitter')
            ->assertSee('KES 8,500.00');
    }

    private function createVacantUnit(string $number, string $type, int $rent): Unit
    {
        return Unit::create([
            'property_id' => $this->property->id,
            'unit_number' => $number,
            'unit_type' => $type,
            'monthly_rent' => $rent,
            'status' => 'vacant',
        ]);
    }

    private function publish(Unit $unit): void
    {
        VacancyListing::create([
            'source' => 'landlord',
            'property_id' => $this->property->id,
            'unit_id' => $unit->id,
            'title' => 'Greenview Apartments · '.$unit->unit_type,
            'place_name' => 'Nairobi',
            'unit_type' => $unit->unit_type,
            'monthly_rent' => $unit->monthly_rent,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
