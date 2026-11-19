<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'description' => '14-day introductory trial.',
                'price' => 0,
                'tenant_limit' => 10,
                'duration_days' => 14,
                'currency' => 'KES',
                'sort_order' => 0,
                'features' => [
                    '14-day free trial',
                    'Up to 10 active tenants',
                    'Property management',
                    'Rent and deposit tracking',
                    'Monthly bills',
                    'Receipt sharing',
                ],
                'is_active' => false,
            ],
            [
                'name' => 'Msingi',
                'slug' => 'msingi',
                'description' => 'Perfect for small rental properties.',
                'price' => 500,
                'tenant_limit' => 10,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 1,
                'features' => [
                    'Up to 10 tenants',
                    'Property management',
                    'Rent and deposit tracking',
                    'Monthly bills',
                    'Receipt sharing',
                    'Basic reports',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Biashara',
                'slug' => 'biashara',
                'description' => 'For growing rental businesses.',
                'price' => 1000,
                'tenant_limit' => 30,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 2,
                'features' => [
                    'Up to 30 tenants',
                    'Multiple properties',
                    'Rent and deposit tracking',
                    'Monthly bills',
                    'Receipt sharing',
                    'Detailed reports',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Ukuaji',
                'slug' => 'ukuaji',
                'description' => 'For established property managers.',
                'price' => 2000,
                'tenant_limit' => 75,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 3,
                'features' => [
                    'Up to 75 tenants',
                    'Multiple properties',
                    'Advanced rent management',
                    'Deposit reconciliation',
                    'Receipt sharing',
                    'Advanced financial reports',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large property portfolios.',
                'price' => 3500,
                'tenant_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 4,
                'features' => [
                    'Unlimited tenants',
                    'Multiple properties',
                    'Complete financial management',
                    'Advanced reporting',
                    'Priority support',
                ],
                'is_active' => true,
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
