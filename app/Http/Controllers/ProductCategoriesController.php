<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;

class ProductCategoriesController extends Controller
{
    public function index()
    {try{
        $categories = ProductCategory::select('id', 'name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);}catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
