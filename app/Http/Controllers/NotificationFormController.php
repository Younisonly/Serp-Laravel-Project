<?php

namespace App\Http\Controllers;

use App\Models\NotificationForm;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NotificationFormController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = NotificationForm::orderBy('category')->orderBy('key');

            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            if ($request->has('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('key', 'like', "%{$s}%")
                      ->orWhere('title_template', 'like', "%{$s}%")
                      ->orWhere('body_template', 'like', "%{$s}%");
                });
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $forms = $request->has('per_page')
                ? $query->paginate($request->per_page)
                : $query->get();

            return response()->json([
                'success' => true,
                'data' => $forms,
            ]);
        } catch (\Exception $e) {
            Log::error('List notification forms failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $form = NotificationForm::findOrFail($id);
            return response()->json(['success' => true, 'data' => $form]);
        } catch (\Exception $e) {
            Log::error('Show notification form failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:100|unique:notification_forms,key',
                'category' => 'required|string|max:50',
                'title_template' => 'required|string|max:255',
                'body_template' => 'required|string|max:5000',
                'default_title' => 'nullable|string|max:255',
                'default_body' => 'nullable|string|max:5000',
                'placeholders' => 'nullable|array',
                'placeholders.*' => 'string|max:50',
                'is_active' => 'boolean',
                'icon' => 'nullable|string|max:50',
                'action_type' => 'nullable|string|max:50',
                'priority' => 'nullable|string|in:low,normal,high',
                'owner_type' => 'nullable|string|in:user,shop',
            ]);

            $form = NotificationForm::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Template created.',
                'data' => $form,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Create notification form failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $form = NotificationForm::findOrFail($id);

            $validated = $request->validate([
                'key' => ['nullable', 'string', 'max:100', Rule::unique('notification_forms', 'key')->ignore($id)],
                'category' => 'nullable|string|max:50',
                'title_template' => 'nullable|string|max:255',
                'body_template' => 'nullable|string|max:5000',
                'default_title' => 'nullable|string|max:255',
                'default_body' => 'nullable|string|max:5000',
                'placeholders' => 'nullable|array',
                'placeholders.*' => 'string|max:50',
                'is_active' => 'boolean',
                'icon' => 'nullable|string|max:50',
                'action_type' => 'nullable|string|max:50',
                'priority' => 'nullable|string|in:low,normal,high',
                'owner_type' => 'nullable|string|in:user,shop',
            ]);

            $form->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Template updated.',
                'data' => $form->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Update notification form failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $form = NotificationForm::findOrFail($id);
            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted.',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete notification form failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function preview(Request $request)
    {
        try {
            $validated = $request->validate([
                'title_template' => 'required|string|max:255',
                'body_template' => 'required|string|max:5000',
                'variables' => 'nullable|array',
            ]);

            $variables = $validated['variables'] ?? [];

            $notifService = app(NotificationService::class);

            $combined = $validated['title_template'] . ' ' . $validated['body_template'];
            $used = $notifService->getPlaceholders($combined);
            $missing = [];
            foreach ($used as $placeholder) {
                if (!array_key_exists($placeholder, $variables)) {
                    $missing[] = $placeholder;
                }
            }

            $renderedTitle = $notifService->safeReplace(
                $validated['title_template'], $variables
            );
            $renderedBody = $notifService->safeReplace(
                $validated['body_template'], $variables
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'rendered_title' => $renderedTitle,
                    'rendered_body' => $renderedBody,
                    'missing_placeholders' => $missing,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Preview notification failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function categories()
    {
        try {
            $categories = NotificationForm::select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('List categories failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
