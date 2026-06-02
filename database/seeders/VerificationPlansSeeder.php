<?php

namespace Database\Seeders;

use App\Models\VerificationPlan;
use App\Models\PlanDiscount;
use Illuminate\Database\Seeder;

class VerificationPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'description' => 'Essential verification for new shops looking to build trust.',
                'price' => 9.99,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Verified badge on your shop profile',
                    'Priority in search results',
                    'Basic analytics dashboard',
                    'Email support',
                ],
                'badge_preview' => ['color' => '#00CCCC', 'icon' => 'verified'],
                'display_order' => 1,
                'is_active' => true,
                'is_recommended' => false,
                'discount' => [
                    'percentage' => 20,
                    'reason_title' => 'Launch Special',
                    'reason_subtitle' => 'First month',
                    'valid_from' => now(),
                    'valid_until' => now()->addMonths(3),
                ],
            ],
            [
                'name' => 'Professional',
                'description' => 'The complete verification package for growing businesses.',
                'price' => 19.99,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Everything in Basic',
                    'Featured in category listings',
                    'Enhanced shop customization',
                    'Priority customer support',
                    'Monthly performance report',
                    'Promotional badges for events',
                ],
                'badge_preview' => ['color' => '#00CCCC', 'icon' => 'verified'],
                'display_order' => 2,
                'is_active' => true,
                'is_recommended' => true,
                'discount' => null,
            ],
            [
                'name' => 'Premium',
                'description' => 'Maximum visibility and growth features for established shops.',
                'price' => 59.99,
                'billing_cycle' => 'monthly',
                'features' => [
                    'Everything in Professional',
                    'Verified badge with priority placement',
                    'Top placement in search results',
                    'Featured on the home page',
                    'Dedicated account manager',
                    'Advanced analytics with insights',
                    'Early access to new features',
                    'Bulk product management tools',
                ],
                'badge_preview' => ['color' => '#FFB84D', 'icon' => 'verified'],
                'display_order' => 3,
                'is_active' => true,
                'is_recommended' => false,
                'discount' => null,
            ],
            [
                'name' => 'Premium Annual',
                'description' => 'All Premium features at a significantly reduced annual rate.',
                'price' => 479.99,
                'billing_cycle' => 'yearly',
                'features' => [
                    'Everything in Premium',
                    '2 months free vs monthly billing',
                    'Priority annual support',
                    'Quarterly business review',
                    'Exclusive webinars and workshops',
                ],
                'badge_preview' => ['color' => '#FFB84D', 'icon' => 'verified'],
                'display_order' => 4,
                'is_active' => true,
                'is_recommended' => false,
                'discount' => [
                    'percentage' => 33,
                    'reason_title' => 'Annual Savings',
                    'reason_subtitle' => 'Save 33% vs monthly',
                    'valid_from' => now(),
                    'valid_until' => now()->addYear(),
                ],
            ],
        ];

        foreach ($plans as $data) {
            $discountData = $data['discount'] ?? null;
            unset($data['discount']);

            $plan = VerificationPlan::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($discountData) {
                PlanDiscount::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'reason_title' => $discountData['reason_title'],
                    ],
                    array_merge($discountData, ['plan_id' => $plan->id, 'is_active' => true])
                );
            }
        }

        echo "   Verification Plans: " . VerificationPlan::count() . "\n";
        echo "   Plan Discounts: " . PlanDiscount::count() . "\n";
    }
}
