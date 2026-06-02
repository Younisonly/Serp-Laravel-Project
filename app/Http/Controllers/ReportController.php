<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Comment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request)
    {
        try {
            $validated = $request->validated();

            $modelClass = match ($validated['table']) {
                'products' => Product::class,
                'shops'    => Shop::class,
                'comments' => Comment::class,
            };

            $reportedItem = $modelClass::findOrFail($validated['reported_id']);

            $report = Report::create([
                'reporter_id'  => Auth::id(),
                'reported_id'  => $validated['reported_id'],
                'report_table' => $validated['table'],
                'content'      => strip_tags($validated['content']),
                'status'       => 'pending',
            ]);

            if ($validated['table'] === 'comments') {
                Comment::where('id', $validated['reported_id'])->increment('reports_count');
            }

            // ─── Fire notification events (reporter identity NEVER exposed) ──
            try {
                $reporter = Auth::user();
                $reason = mb_substr(strip_tags($validated['content']), 0, 100);

                $action = match ($validated['table']) {
                    'comments' => 'report_comment',
                    'shops' => 'report_shop',
                    'products' => 'report_product',
                };

                $extra = [
                    'reason' => $reason,
                    'report' => $report,
                ];

                match ($validated['table']) {
                    'comments' => $extra['comment'] = $reportedItem,
                    'products' => $extra['product'] = $reportedItem,
                    default => null,
                };

                if ($validated['table'] === 'comments') {
                    $comment = $reportedItem;
                    $shop = Shop::find($comment->commented_id);
                    if ($shop) $extra['shop'] = $shop;
                } elseif ($validated['table'] === 'shops') {
                    $extra['shop'] = $reportedItem;
                } elseif ($validated['table'] === 'products') {
                    $extra['product'] = $reportedItem;
                    $extra['shop'] = Shop::find($reportedItem->shop_id);
                }

                // Notify the reporter with confirmation
                try {
                    $notifService = app(NotificationService::class);
                    $notifService->deliver($reporter->id, 'your_report_received', [
                        'user_name' => $reporter->name,
                    ], [
                        'sender' => 'system',
                        'owner_type' => 'user',
                        'data' => ['report_id' => $report->id],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Reporter confirmation notification skipped: ' . $e->getMessage());
                }

                // Notify the owner (reporter identity stripped inside listener)
                NotificationTriggered::dispatch($action, $extra);
            } catch (\Throwable $notifErr) {
                Log::warning('Report notification event failed (non-critical): ' . $notifErr->getMessage());
            }

            return response()->json([
                'message' => 'Report submitted successfully.',
                'report'  => $report,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Report store failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
