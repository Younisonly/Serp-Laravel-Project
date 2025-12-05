<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentReactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\ShopsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\nearbyShopController;
use App\Http\Controllers\notificationController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductsFillterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShopCategoryController;
use App\Http\Controllers\shopRegisteration;
use App\Http\Controllers\updateUserInfo;
use App\Models\cities;

Route::apiResource('users', UserController::class);
Route::apiResource('shops', ShopsController::class);

////////////////////main map nearby ////////////////////////////////
Route::get('/nearby', [NearbyShopController::class, 'nearbyShops']);



////////////////////products of a shop///////////////////////////
Route::get('/shopsProduct', [ProductsController::class, 'productsOfShop']);
Route::get('/product/{id}', [ProductsController::class, 'getProductById']);
Route::get('/shop/{id}', [ShopsController::class, 'getShopById']);


///////////////////// Auth api///////////////////////////////////
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/flutter-user/{id}', [UserController::class, 'flutterUserInfo']);
    Route::post('/updateUserInfo', [updateUserInfo::class, 'update'])->middleware('throttle:60,1');;
    Route::get('/shopByUserId/{id}', [ShopsController::class, 'getShopByUserId']);
});

Route::get('/comments', [CommentController::class,  'index']);

Route::get('/product-categories', [ProductCategoriesController::class, 'index']);
Route::get('Products-filtering', [ProductsFillterController::class, 'Productfilter']);


Route::get('search-shops', [SearchController::class, 'search']);

Route::post('/comments', [CommentController::class, 'store']);


Route::middleware('auth:sanctum')->group(function () {


    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    Route::post('/comment-reactions', [CommentReactionController::class, 'store']);
    Route::post('/reports', [ReportController::class, 'store']);
});


/////////////////// cities && countries

Route::get('/countries', [CountriesController::class, 'index']);
Route::get('/countries-with-cities', [CountriesController::class, 'withCities']);
Route::get('/cities', [CitiesController::class, 'index']);
Route::get('/countries/{countryId}/cities', [CitiesController::class, 'byCountry']);


/////////////////////notification ///////////////////////
Route::post('send-notification', [notificationController::class, 'store'])->middleware('auth:sanctum');




Route::middleware('auth:sanctum')->group(function () {
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::post('/RegisterShop', [shopRegisteration::class, 'store']);
});





Route::get('/shop-categories', [ShopCategoryController::class, 'index']);
Route::get('/shop-categories/{id}', [ShopCategoryController::class, 'show']);
Route::post('/shop-categories', [ShopCategoryController::class, 'store']);
Route::put('/shop-categories/{id}', [ShopCategoryController::class, 'update']);
Route::delete('/shop-categories/{id}', [ShopCategoryController::class, 'destroy']);
