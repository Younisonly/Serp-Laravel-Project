<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\shops;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    
    public function getShopById($id)
    {
        try {
            $shop = Shop::find($id);
    
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found'
                ], 404);
            }
    
            /* ---------- convert fields safely ---------- */
            $fieldsToFormat = ['created_at', 'updated_at', 'deleted_at', 'open_at', 'close_at'];
    
            foreach ($fieldsToFormat as $field) {
                if (isset($shop->$field) && $shop->$field !== null) {
                    // Only convert to string, no Carbon parsing
                    $shop->$field = (string) $shop->$field;
                }
            }
    
            return response()->json([
                'success' => true,
                'data'    => $shop
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function getShopByUserId($id)
    {
        try {
            /* ---------- look for the shop that belongs to the owner ---------- */
            $shop = Shop::where('owner_id', $id)->first();
    
            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop with this owner id is not found'
                ], 404);
            }
    
            // /* ---------- convert fields safely ---------- */
            // $fieldsToFormat = ['created_at', 'updated_at', 'deleted_at', 'open_at', 'close_at'];
    
            // foreach ($fieldsToFormat as $field) {
            //     if (isset($shop->$field) && $shop->$field !== null) {
            //         $shop->$field = (string) $shop->$field;
            //     }
            // }
    
            return response()->json([
                'success' => true,
                'data'    => $shop
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }}
