<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        try {
            $templates = Cache::remember('notification_templates_active', 3600, function () {
                return NotificationTemplate::active()->get();
            });

            return response()->json([
                'status' => 'success',
                'data' => $templates,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[NotificationTemplateController] index failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $template = NotificationTemplate::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $template,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[NotificationTemplateController] show failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Notification template not found.',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:100|unique:notification_templates,key',
                'title_en' => 'required|string|max:255',
                'title_ar' => 'required|string|max:255',
                'content_en' => 'required|string|max:5000',
                'content_ar' => 'required|string|max:5000',
                'icon' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            $template = NotificationTemplate::create($validated);
            Cache::forget('notification_templates_active');

            return response()->json([
                'status' => 'success',
                'message' => 'Notification template created.',
                'data' => $template,
            ], 201);

        } catch (Throwable $e) {
            Log::error('[NotificationTemplateController] store failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $template = NotificationTemplate::findOrFail($id);

            $validated = $request->validate([
                'key' => ['nullable', 'string', 'max:100', Rule::unique('notification_templates', 'key')->ignore($id)],
                'title_en' => 'nullable|string|max:255',
                'title_ar' => 'nullable|string|max:255',
                'content_en' => 'nullable|string|max:5000',
                'content_ar' => 'nullable|string|max:5000',
                'icon' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            $template->update($validated);
            Cache::forget('notification_templates_active');

            return response()->json([
                'status' => 'success',
                'message' => 'Notification template updated.',
                'data' => $template->fresh(),
            ], 200);

        } catch (Throwable $e) {
            Log::error('[NotificationTemplateController] update failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $template = NotificationTemplate::findOrFail($id);
            $template->delete();
            Cache::forget('notification_templates_active');

            return response()->json([
                'status' => 'success',
                'message' => 'Notification template deleted.',
            ], 200);

        } catch (Throwable $e) {
            Log::error('[NotificationTemplateController] destroy failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function clearCache()
    {
        Cache::forget('notification_templates_active');
        return response()->json(['status' => 'success', 'message' => 'Cache cleared.']);
    }
}
