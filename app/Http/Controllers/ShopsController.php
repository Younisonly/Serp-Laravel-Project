<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\shops;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Favorite;

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
    }
    
    
    
    // GET /shop/owner/{id}
public function getByOwner($id) {
    $shop = Shop::where('owner_id', $id)->first();
    if (!$shop) return response()->json(['success'=>false,'message'=>'Not found'],404);
    return response()->json(['success'=>true,'data'=>$shop]);
}

// POST /shop/{id}/status
public function updateStatus(Request $r, $id) {
    $shop = Shop::findOrFail($id);
    /** @var \App\Models\User $user */
    if ($shop->owner_id != auth('sanctum')->id()) return response()->json(['success'=>false,'message'=>'Unauthorized'],403);
    $shop->update(['status' => $r->status]);
    return response()->json(['success'=>true,'data'=>$shop]);
}

// GET /shop/{id}/analytics
public function getAnalytics($id) {
    $shop = Shop::findOrFail($id);
    return response()->json(['success'=>true,'data'=>['total_views'=>$shop->views,'total_favorites'=>$shop->followers_number,'total_comments'=>Comment::where('shop_id',$id)->count()]]);
}

// GET /shop/{id}/comments?limit=3
public function getComments($id, Request $r) {
    $comments = Comment::where('shop_id',$id)->whereNull('parent_id')->with('user:id,name')->latest()->limit($r->get('limit',3))->get();
    return response()->json(['success'=>true,'data'=>$comments]);
}

// GET /shop/{id}/insights
public function getInsights($id) {
    return response()->json(['success'=>true,'data'=>['favorited_count'=>Favorite::where('favorable_type','App\Models\Shop')->where('favorable_id',$id)->count(),'viewed_count'=>0,'profile_visits'=>0]]); // Extend later
}
    
    }
