<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'description' => 'Explore Pango free for 14 days.',
                'price' => 0,
                'tenant_limit' => 20,
                'property_limit' => null,
                'duration_days' => 14,
                'currency' => 'KES',
                'sort_order' => 0,
                'features' => [
                    '20 active tenants',
                    'Unlimited properties',
                    'Property management',
                    'Unit management',
                    'Tenant management',
                ],
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Ideal for small rental businesses.',
                'price' => 500,
                'tenant_limit' => 20,
                'property_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 1,
                'features' => [
                    '20 active tenants',
                    'Unlimited properties',
                    'Rent management',
                    'Deposit management',
                    'Monthly bills',
                ],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'For growing property portfolios.',
                'price' => 1000,
                'tenant_limit' => 45,
                'property_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 2,
                'features' => [
                    '45 active tenants',
                    'Unlimited properties',
                    'Rent management',
                    'Deposit management',
                    'Monthly bills',
                    'Reports',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For established landlords.',
                'price' => 1500,
                'tenant_limit' => 100,
                'property_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 3,
                'features' => [
                    '100 active tenants',
                    'Unlimited properties',
                    'Rent management',
                    'Deposit management',
                    'Monthly bills',
                    'Reports',
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For larger rental businesses.',
                'price' => 2000,
                'tenant_limit' => 150,
                'property_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 4,
                'features' => [
                    '150 active tenants',
                    'Unlimited properties',
                    'Rent management',
                    'Deposit management',
                    'Monthly bills',
                    'Reports',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Complete flexibility for large portfolios.',
                'price' => 6000,
                'tenant_limit' => null,
                'property_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 5,
                'features' => [
                    'Unlimited tenants',
                    'Unlimited properties',
                    'Rent management',
                    'Deposit management',
                    'Monthly bills',
                    'Reports',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
