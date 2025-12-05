<?php

namespace App\Http\Controllers;

use App\Models\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ShopCategoryController extends Controller
{
    // 🟢 Get all categories
    public function index()
    {
        try {
            $categories = ShopCategory::all();

            return response()->json([
                'status'  => 'success',
                'message' => 'Categories retrieved successfully',
                'data'    => $categories,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch categories',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // 🔵 Get one category by ID
    public function show($id)
    {
        try {
            $category = ShopCategory::find($id);

            if (!$category) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Category retrieved successfully',
                'data'    => $category,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // 🟡 Add a new category
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'provides' => 'required|numeric|in:1,2,3',
            ]);

            $category = ShopCategory::create($validated);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Category created successfully',
                'data'    => $category,
            ], 201);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // 🟠 Update a category
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $category = ShopCategory::find($id);

            if (!$category) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'provides' => 'sometimes|numeric|in:1,2,3',
            ]);

            $category->update($validated);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Category updated successfully',
                'data'    => $category,
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // 🔴 Delete a category
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $category = ShopCategory::find($id);

            if (!$category) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Category deleted successfully',
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
