<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use App\Models\Product;

class ShopOwnerPolicy
{
    /**
     * Determine if the user owns the shop.
     */
    public function ownsShop(User $user, Shop $shop): bool
    {
        return $shop->owner_id === $user->id;
    }

    /**
     * Determine if the user owns the product's shop.
     */
    public function ownsProduct(User $user, Product $product): bool
    {
        return $product->shop->owner_id === $user->id;
    }

    /**
     * Determine if the user is a shop owner (has at least one shop).
     */
    public function isShopOwner(User $user): bool
    {
        return Shop::where('owner_id', $user->id)->exists();
    }

    /**
     * Get the shop owned by the user.
     */
    public function getUserShop(User $user): ?Shop
    {
        return Shop::where('owner_id', $user->id)->first();
    }
}
