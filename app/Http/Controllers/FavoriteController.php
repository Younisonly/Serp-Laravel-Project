<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    private function cleanModel($model)
    {
        foreach (['created_at', 'updated_at', 'deleted_at'] as $f) {
            if (isset($model->$f) && $model->$f) {
                $model->$f = \Carbon\Carbon::parse($model->$f)->toIso8601String();
            }
        }
        unset($model->open_at, $model->close_at);
        return $model;
    }

    public function index(Request $request)
{
    $type = $request->query('type'); // shops | products
    $user = Auth::user();

    if ($type === 'shops') {
        $favs = Favorite::with('favorable.images')
            ->where('user_id', $user->id)
            ->where('favorable_type', Shop::class)
            ->get();
    } else {
        $favs = Favorite::with('favorable.primaryImage')
            ->where('user_id', $user->id)
            ->where('favorable_type', Product::class)
            ->get();
    }

    $mini = $favs->filter(fn($f) => $f->favorable !== null)
        ->map(function ($f) use ($type) {
            $m = $this->cleanModel($f->favorable);

            if ($type === 'shops') {
                // ✅ Just use the logo field directly (text only)
                return [
                    'id'         => $m->id,
                    'name'       => $m->name,
                    'logo'       => $m->logo, // raw text from DB
                    'status'     => $m->status,
                    'stars_rate' => $m->stars_rate ?? 0,
                ];
            }

            // ✅ For products, get the primary image’s `url` (raw text)
            $image = $m->primaryImage ? $m->primaryImage->url : null;

            return [
                'id'         => $m->id,
                'name'       => $m->name,
                'image'      => $image, // raw text from DB
                'price'      => $m->price ?? 0,
                'stars_rate' => $m->stars_rate ?? 0,
            ];
        });

    return response()->json($mini->values());
}

    public function toggle(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|in:shop,product'
        ]);

        $user  = Auth::user();
        $class = $request->type === 'shop' ? Shop::class : Product::class;
        $model = $class::findOrFail($request->id);

        $favorite = Favorite::where([
            'user_id'        => $user->id,
            'favorable_id'   => $model->id,
            'favorable_type' => $class
        ])->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'message' => 'removed',
                'data'    => $this->cleanModel($model)
            ]);
        }

        Favorite::create([
            'user_id'        => $user->id,
            'favorable_id'   => $model->id,
            'favorable_type' => $class
        ]);

        return response()->json([
            'message' => 'added',
            'data'    => $this->cleanModel($model)
        ]);
    }
}
