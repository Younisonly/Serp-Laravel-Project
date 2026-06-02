<?php

namespace App\Http\Controllers;

use App\Models\VerificationPlan;
use App\Models\PlanDiscount;
use App\Models\ShopSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    // ─── PUBLIC ENDPOINTS ───────────────────────────────────────────────────

    public function plans(Request $request)
    {
        try {
            $plans = VerificationPlan::with(['activeDiscounts'])
                ->active()
                ->get()
                ->map(fn($plan) => $this->formatPlan($plan));

            return response()->json([
                'success' => true,
                'data' => $plans,
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch verification plans failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function mySubscription(Request $request)
    {
        try {
            $shopId = $request->input('shop_id');
            if (!$shopId) {
                return response()->json(['success' => false, 'message' => 'Shop ID required.'], 422);
            }

            $subscription = ShopSubscription::with('plan')
                ->where('shop_id', $shopId)
                ->latest()
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->plan?->name,
                    'status' => $subscription->status,
                    'started_at' => $subscription->started_at?->toISOString(),
                    'expires_at' => $subscription->expires_at?->toISOString(),
                    'cancelled_at' => $subscription->cancelled_at?->toISOString(),
                    'benefits' => $subscription->plan?->features,
                    'badge_preview' => $subscription->plan?->badge_preview,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch subscription failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── ADMIN ENDPOINTS ────────────────────────────────────────────────────

    public function adminPlans(Request $request)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $plans = VerificationPlan::with(['discounts'])
                ->orderBy('display_order')
                ->get()
                ->map(fn($plan) => $this->formatPlanForAdmin($plan));

            return response()->json(['success' => true, 'data' => $plans]);
        } catch (\Exception $e) {
            Log::error('Admin plans failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function storePlan(Request $request)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,lifetime',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'badge_preview' => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_recommended' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $plan = VerificationPlan::create($validator->validated());
            return response()->json([
                'success' => true,
                'message' => 'Plan created.',
                'data' => $this->formatPlanForAdmin($plan),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Store plan failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function updatePlan(Request $request, $planId)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $plan = VerificationPlan::findOrFail($planId);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'billing_cycle' => 'sometimes|in:monthly,yearly,lifetime',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'badge_preview' => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_recommended' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $plan->update($validator->validated());
            return response()->json([
                'success' => true,
                'message' => 'Plan updated.',
                'data' => $this->formatPlanForAdmin($plan->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Update plan failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function deletePlan(Request $request, $planId)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $plan = VerificationPlan::findOrFail($planId);
            $plan->delete();
            return response()->json(['success' => true, 'message' => 'Plan deleted.']);
        } catch (\Exception $e) {
            Log::error('Delete plan failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function reorderPlans(Request $request)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:verification_plans,id',
            'order.*.display_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            foreach ($request->input('order') as $item) {
                VerificationPlan::where('id', $item['id'])->update(['display_order' => $item['display_order']]);
            }
            return response()->json(['success' => true, 'message' => 'Order updated.']);
        } catch (\Exception $e) {
            Log::error('Reorder plans failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── ADMIN DISCOUNTS ────────────────────────────────────────────────────

    public function storeDiscount(Request $request)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|integer|exists:verification_plans,id',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|integer|min:0|max:100',
            'reason_title' => 'nullable|string|max:255',
            'reason_subtitle' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $discount = PlanDiscount::create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Discount created.', 'data' => $discount], 201);
        } catch (\Exception $e) {
            Log::error('Store discount failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function updateDiscount(Request $request, $discountId)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $discount = PlanDiscount::findOrFail($discountId);

        $validator = Validator::make($request->all(), [
            'plan_id' => 'sometimes|integer|exists:verification_plans,id',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|integer|min:0|max:100',
            'reason_title' => 'nullable|string|max:255',
            'reason_subtitle' => 'nullable|string|max:255',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $discount->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Discount updated.', 'data' => $discount->fresh()]);
        } catch (\Exception $e) {
            Log::error('Update discount failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function deleteDiscount(Request $request, $discountId)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $discount = PlanDiscount::findOrFail($discountId);
            $discount->delete();
            return response()->json(['success' => true, 'message' => 'Discount deleted.']);
        } catch (\Exception $e) {
            Log::error('Delete discount failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── FORMATTERS ─────────────────────────────────────────────────────────

    private function formatPlan($plan)
    {
        $discount = $plan->activeDiscounts->first();

        $data = [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'price' => (float) $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'features' => $plan->features ?? [],
            'badge_preview' => $plan->badge_preview,
            'display_order' => $plan->display_order,
            'is_recommended' => $plan->is_recommended,
        ];

        if ($discount) {
            $data['discount'] = [
                'id' => $discount->id,
                'amount' => $discount->amount ? (float) $discount->amount : null,
                'percentage' => $discount->percentage,
                'reason_title' => $discount->reason_title,
                'reason_subtitle' => $discount->reason_subtitle,
            ];

            if ($discount->percentage) {
                $data['discounted_price'] = round($plan->price * (1 - $discount->percentage / 100), 2);
            } elseif ($discount->amount) {
                $data['discounted_price'] = max(0, $plan->price - $discount->amount);
            }
        }

        return $data;
    }

    private function formatPlanForAdmin($plan)
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'price' => (float) $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'features' => $plan->features ?? [],
            'badge_preview' => $plan->badge_preview,
            'display_order' => $plan->display_order,
            'is_active' => $plan->is_active,
            'is_recommended' => $plan->is_recommended,
            'discounts' => $plan->discounts->map(fn($d) => [
                'id' => $d->id,
                'amount' => $d->amount ? (float) $d->amount : null,
                'percentage' => $d->percentage,
                'reason_title' => $d->reason_title,
                'reason_subtitle' => $d->reason_subtitle,
                'valid_from' => $d->valid_from?->toISOString(),
                'valid_until' => $d->valid_until?->toISOString(),
                'is_active' => $d->is_active,
            ]),
            'created_at' => $plan->created_at?->toISOString(),
            'updated_at' => $plan->updated_at?->toISOString(),
        ];
    }
}
