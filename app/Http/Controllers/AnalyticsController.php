<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Follower;
use App\Models\Image;
use App\Models\Like;
use App\Models\Product;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    /**
     * Get detailed analytics for a shop
     */
    public function detailedAnalytics(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $period = $request->get('period', '30');
            $startDate = now()->subDays((int) $period);

            // Views over time (using the new views tracking table)
            $viewsOverTime = View::where('viewable_type', Shop::class)
                ->where('viewable_id', $shopId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ]);

            $periodViews = $viewsOverTime->sum('count');

            // Comments over time + total + new
            $commentsOverTime = Comment::where('commentable_type', 'shops')
                ->where('commented_id', $shopId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ]);

            $totalComments = Comment::where('commentable_type', 'shops')
                ->where('commented_id', $shopId)
                ->count();

            $newComments = $commentsOverTime->sum('count');

            // Favorites over time + total + new
            $favoritesOverTime = Favorite::where('favorable_type', Shop::class)
                ->where('favorable_id', $shopId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ]);

            $totalFavorites = Favorite::where('favorable_type', Shop::class)
                ->where('favorable_id', $shopId)
                ->count();

            $newFavorites = $favoritesOverTime->sum('count');

            // Followers over time + total + new (from followers table)
            $followersOverTime = Follower::where('followed_shop_id', $shopId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ]);

            $totalFollowers = $shop->followers_number ?? 0;
            $newFollowers = $followersOverTime->sum('count');

            // Likes over time + total + new (from likes table)
            $likesOverTime = Like::where('likeable_type', Shop::class)
                ->where('likeable_id', $shopId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ]);

            $totalLikes = $shop->likes()->count();
            $newLikes = $likesOverTime->sum('count');

            // Rating distribution
            $ratingDistribution = DB::table('raters')
                ->where('table', 'shops')
                ->where('rated_id', $shopId)
                ->selectRaw('stars, COUNT(*) as count')
                ->groupBy('stars')
                ->orderBy('stars', 'desc')
                ->get()
                ->map(fn($item) => [
                    'stars' => (int) $item->stars,
                    'count' => (int) $item->count,
                ]);

            // Rating over time (daily granularity — frontend aggregates)
            $ratingOverTime = DB::table('raters')
                ->where('table', 'shops')
                ->where('rated_id', $shopId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, ROUND(AVG(stars), 1) as avg_rating, COUNT(*) as count')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'avg_rating' => (float) $item->avg_rating,
                    'count' => (int) $item->count,
                ]);

            // Product performance
            $topProducts = Product::where('shop_id', $shopId)
                ->orderBy('views', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'views', 'stars_rate'])
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'views' => $p->views,
                    'rating' => (float) $p->stars_rate,
                ]);

            // Image gallery stats
            $totalImages = Image::where('table', 'shops')
                ->where('owner_id', $shopId)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_views' => $shop->views ?? 0,
                        'total_favorites' => $totalFavorites,
                        'total_followers' => $totalFollowers,
                        'total_likes' => $totalLikes,
                        'total_comments' => $totalComments,
                        'total_images' => $totalImages,
                        'total_products' => Product::where('shop_id', $shopId)->count(),
                        'rating' => (float) $shop->stars_rate,
                        'raters_count' => $shop->raters_number ?? 0,
                    ],
                    'period' => [
                        'days' => (int) $period,
                        'new_views' => $periodViews,
                        'new_comments' => $newComments,
                        'new_favorites' => $newFavorites,
                        'new_followers' => $newFollowers,
                        'new_likes' => $newLikes,
                    ],
                    'views_over_time' => $viewsOverTime,
                    'comments_over_time' => $commentsOverTime,
                    'favorites_over_time' => $favoritesOverTime,
                    'followers_over_time' => $followersOverTime,
                    'likes_over_time' => $likesOverTime,
                    'rating_distribution' => $ratingDistribution,
                    'rating_over_time' => $ratingOverTime,
                    'top_products' => $topProducts,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Detailed analytics failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Get quick stats summary
     */
    public function summary(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $commentCounts = Comment::where('commentable_type', 'shops')
                ->where('commented_id', $shopId)
                ->where('status', 'active')
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN reply_content IS NULL THEN 1 ELSE 0 END) as unreplied')
                ->first();

            $totalComments = (int) ($commentCounts->total ?? 0);
            $unrepliedComments = (int) ($commentCounts->unreplied ?? 0);

            $totalFavorites = Favorite::where('favorable_type', Shop::class)
                ->where('favorable_id', $shopId)
                ->count();

            $totalProducts = Product::where('shop_id', $shopId)->count();
            $totalImages = Image::where('table', 'shops')->where('owner_id', $shopId)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'views' => $shop->views ?? 0,
                    'favorites' => $totalFavorites,
                    'comments' => $totalComments,
                    'unreplied_comments' => $unrepliedComments,
                    'products' => $totalProducts,
                    'images' => $totalImages,
                    'rating' => (float) $shop->stars_rate,
                    'raters_count' => $shop->raters_number ?? 0,
                    'status' => $shop->status,
                    'verification_status' => $shop->verification_status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics summary failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
