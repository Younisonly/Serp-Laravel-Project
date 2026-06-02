<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentReactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\ShopsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NearbyShopController;
use App\Http\Controllers\notificationController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductsFillterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BusinessTypeController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\shopRegisteration;
use App\Http\Controllers\updateUserInfo;
use App\Http\Controllers\AppSettingsController;
use App\Http\Controllers\ImageSettingsController;
use App\Http\Controllers\ShopManagementController;
use App\Http\Controllers\ProductManagementController;
use App\Http\Controllers\ImageManagementController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ShopNotificationController;
use App\Http\Controllers\NotificationFormController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\EntityCheckController;
use App\Models\cities;

// ================= PUBLIC ROUTES =================
Route::apiResource('users', UserController::class)->middleware('throttle:api');
Route::apiResource('shops', ShopsController::class)->middleware('throttle:api');

// Main map nearby
Route::get('/nearby', [NearbyShopController::class, 'nearbyShops'])->middleware('throttle:api');

// ================= NOTIFICATION ROUTES (must be before /shop/{id} to avoid conflict) =================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/shop/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/shop/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::put('/shop/notifications/{notificationId}/read', [App\Http\Controllers\NotificationController::class, 'markRead']);
    Route::put('/shop/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllRead']);
    Route::delete('/shop/notifications/{notificationId}', [App\Http\Controllers\NotificationController::class, 'destroy']);
});

// ================= NOTIFICATION TEMPLATES (admin) =================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notification-forms', [App\Http\Controllers\NotificationFormController::class, 'index']);
    Route::get('/notification-forms/categories', [App\Http\Controllers\NotificationFormController::class, 'categories']);
    Route::get('/notification-forms/{id}', [App\Http\Controllers\NotificationFormController::class, 'show']);
    Route::post('/notification-forms', [App\Http\Controllers\NotificationFormController::class, 'store']);
    Route::put('/notification-forms/{id}', [App\Http\Controllers\NotificationFormController::class, 'update']);
    Route::delete('/notification-forms/{id}', [App\Http\Controllers\NotificationFormController::class, 'destroy']);
    Route::post('/notification-forms/preview', [App\Http\Controllers\NotificationFormController::class, 'preview']);
});

// Products of a shop
Route::get('/shopsProduct', [ProductsController::class, 'productsOfShop'])->middleware('throttle:api');
Route::get('/product/{id}', [ProductsController::class, 'getProductById'])->middleware('throttle:api');
Route::get('/shop/{id}', [ShopsController::class, 'getShopById'])->middleware('throttle:api');

// View tracking with IP dedup and rate limiting
Route::post('/product/{id}/view', [App\Http\Controllers\ViewController::class, 'product'])->middleware('throttle:views');
Route::post('/shop/{id}/view', [App\Http\Controllers\ViewController::class, 'shop'])->middleware('throttle:views');

// ================= AUTH API =================
// Public auth routes (no token required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
});

// ================= PROTECTED ROUTES (auth:sanctum) =================
Route::middleware('auth:sanctum')->group(function () {
    
    // ✅ Auth endpoints that need protection (FULL PATHS):
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // User routes
    Route::get('/flutter-user/{id}', [UserController::class, 'flutterUserInfo']);
    Route::post('/updateUserInfo', [updateUserInfo::class, 'update']);
    
    // Shop routes
    Route::get('/shopByUserId/{id}', [ShopsController::class, 'getShopByUserId']);
    
    // Comment routes
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    
    // Reaction & Report routes
    Route::post('/comment-reactions', [CommentReactionController::class, 'store']);
    Route::post('/reports', [ReportController::class, 'store']);
    
    // Notification routes
    Route::post('send-notification', [notificationController::class, 'store']);
    
    // Like routes (polymorphic: product, shop, user)
    Route::post('likes/toggle', [LikeController::class, 'toggle']);

    // Follow routes
    Route::post('/shop/{shopId}/follow', [App\Http\Controllers\FollowController::class, 'toggle']);
    
    // Shop registration
    Route::post('/RegisterShop', [shopRegisteration::class, 'store']);


    //shop management functiosn
    Route::get('/shop/owner/{id}', [ShopsController::class, 'getByOwner']);
    Route::post('/shop/{id}/status', [ShopsController::class, 'updateStatus']);
    Route::get('/shop/{id}/analytics', [ShopsController::class, 'getAnalytics']);
    Route::get('/shop/{id}/comments', [ShopsController::class, 'getComments']);
    Route::post('/comment/{id}/reply', [CommentController::class, 'reply']);
    Route::put('/comment/{id}/reply', [ShopsController::class, 'updateReply']);
    Route::delete('/comment/{id}/reply', [ShopsController::class, 'deleteReply']);
    Route::get('/shop/{id}/insights', [ShopsController::class, 'getInsights']);

    // ==========================================
    // SHOP MANAGEMENT (Owner Dashboard)
    // ==========================================

    // Shop Profile Management
    Route::get('/shop/management/my-shop', [ShopManagementController::class, 'myShop']);
    Route::put('/shop/management/{id}', [ShopManagementController::class, 'updateShop']);
    Route::put('/shop/management/{id}/hours', [ShopManagementController::class, 'updateHours']);
    Route::delete('/shop/management/{id}', [ShopManagementController::class, 'deleteShop']);
    Route::get('/shop/management/{id}/verification', [ShopManagementController::class, 'verificationStatus']);

    // Product Management (CRUD)
    Route::get('/shop/management/{shopId}/products', [ProductManagementController::class, 'index']);
    Route::post('/shop/management/{shopId}/products', [ProductManagementController::class, 'store']);
    Route::get('/shop/management/{shopId}/products/{productId}', [ProductManagementController::class, 'show']);
    Route::put('/shop/management/{shopId}/products/{productId}', [ProductManagementController::class, 'update']);
    Route::delete('/shop/management/{shopId}/products/{productId}', [ProductManagementController::class, 'destroy']);
    Route::post('/shop/management/{shopId}/products/bulk-delete', [ProductManagementController::class, 'bulkDelete']);
    Route::get('/shop/management/{shopId}/products/stats', [ProductManagementController::class, 'stats']);

    // Image Management
    Route::get('/shop/management/{shopId}/images', [ImageManagementController::class, 'index']);
    Route::post('/shop/management/{shopId}/images', [ImageManagementController::class, 'upload']);
    Route::put('/shop/management/{shopId}/images/{imageId}/primary', [ImageManagementController::class, 'setPrimary']);
    Route::delete('/shop/management/{shopId}/images/{imageId}', [ImageManagementController::class, 'destroy']);
    Route::post('/shop/management/{shopId}/images/reorder', [ImageManagementController::class, 'reorder']);

    // Analytics
    Route::get('/shop/management/{shopId}/analytics/detailed', [AnalyticsController::class, 'detailedAnalytics']);
    Route::get('/shop/management/{shopId}/analytics/summary', [AnalyticsController::class, 'summary']);

    // Entity existence check (lightweight)
    Route::get('/entity-check/{type}/{id}', [EntityCheckController::class, 'check']);

    // Support Tickets
    Route::get('/support/tickets', [SupportController::class, 'index']);
    Route::post('/support/tickets', [SupportController::class, 'store']);
    Route::get('/support/tickets/{ticketId}', [SupportController::class, 'show']);
    Route::get('/support/tickets/unread-count', [SupportController::class, 'unreadCount']);
    Route::post('/support/tickets/{ticketId}/reply', [SupportController::class, 'reply']);
    Route::post('/support/tickets/{ticketId}/resolve', [SupportController::class, 'resolve']);
    Route::post('/support/tickets/{ticketId}/reopen', [SupportController::class, 'reopen']);
    Route::delete('/support/tickets/{ticketId}', [SupportController::class, 'deleteTicket']);

    // Admin routes — requires platform-level access
    Route::get('/admin/support/tickets', [SupportController::class, 'allTickets']);
    Route::patch('/admin/support/tickets/{ticketId}/status', [SupportController::class, 'updateStatus']);

    // Verification & Subscription Plans
    Route::get('/verification/plans', [VerificationController::class, 'plans']);
    Route::get('/verification/my-subscription', [VerificationController::class, 'mySubscription']);

    // Admin Verification Management
    Route::get('/admin/verification/plans', [VerificationController::class, 'adminPlans']);
    Route::post('/admin/verification/plans', [VerificationController::class, 'storePlan']);
    Route::put('/admin/verification/plans/{planId}', [VerificationController::class, 'updatePlan']);
    Route::delete('/admin/verification/plans/{planId}', [VerificationController::class, 'deletePlan']);
    Route::put('/admin/verification/plans/reorder', [VerificationController::class, 'reorderPlans']);
    Route::post('/admin/verification/discounts', [VerificationController::class, 'storeDiscount']);
    Route::put('/admin/verification/discounts/{discountId}', [VerificationController::class, 'updateDiscount']);
    Route::delete('/admin/verification/discounts/{discountId}', [VerificationController::class, 'deleteDiscount']);

});

// ================= MORE PUBLIC ROUTES =================
// Favorite routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('favorites', [FavoriteController::class, 'index'])->middleware('throttle:favorites');
    Route::post('favorites/toggle', [FavoriteController::class, 'toggle'])->middleware('throttle:favorites');
});

Route::get('/likes/count', [LikeController::class, 'count']);
Route::get('/shop/{shopId}/follow/status', [App\Http\Controllers\FollowController::class, 'status']);
Route::get('/comments', [CommentController::class, 'index']);
Route::get('/product-categories', [ProductCategoriesController::class, 'index']);
Route::get('Products-filtering', [ProductsFillterController::class, 'Productfilter']);
Route::get('search-shops', [SearchController::class, 'search']);

// ================= APP SETTINGS =================
Route::get('/settings', [AppSettingsController::class, 'index']);
Route::get('/image-settings/{type}', [ImageSettingsController::class, 'show']);

// ================= CITIES & COUNTRIES =================
Route::get('/countries', [CountriesController::class, 'index']);
Route::get('/countries-with-cities', [CountriesController::class, 'withCities']);
Route::get('/cities', [CitiesController::class, 'index']);
Route::get('/countries/{countryId}/cities', [CitiesController::class, 'byCountry']);

// ================= NOTIFICATION TEMPLATES =================
Route::get('/notification-templates', [NotificationTemplateController::class, 'index']);
Route::get('/notification-templates/{id}', [NotificationTemplateController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/notification-templates', [NotificationTemplateController::class, 'store']);
    Route::put('/notification-templates/{id}', [NotificationTemplateController::class, 'update']);
    Route::delete('/notification-templates/{id}', [NotificationTemplateController::class, 'destroy']);
    Route::post('/notification-templates/cache/clear', [NotificationTemplateController::class, 'clearCache']);
});

// ================= BUSINESS TYPES =================
Route::get('/business-types', [BusinessTypeController::class, 'index']);
Route::get('/business-types/{id}', [BusinessTypeController::class, 'show']);
Route::post('/business-types/cache/clear', [BusinessTypeController::class, 'clearCache']);

// ================= AMENITIES =================
Route::get('/amenities', [AmenityController::class, 'index']);
Route::get('/amenities/{id}', [AmenityController::class, 'show']);
Route::post('/amenities/cache/clear', [AmenityController::class, 'clearCache']);