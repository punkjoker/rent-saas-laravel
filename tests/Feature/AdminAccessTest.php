<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_admin_portal_redirects_guests_to_its_login_page(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_form_is_available_at_the_management_url(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Administrator sign in');
    }

    public function test_landlord_session_can_reach_admin_login_form(): void
    {
        $landlordUser = new User([
            'name' => 'Landlord',
            'email' => 'landlord@example.test',
            'role' => 'landlord',
        ]);

        $this->actingAs($landlordUser)
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Administrator sign in');
    }
}
