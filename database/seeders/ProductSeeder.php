<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Product;
use App\Models\Image;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private array $brands = ['Samsung', 'Apple', 'Sony', 'LG', 'Philips', 'Bosch', 'Panasonic', 'Xiaomi', 'Nike', 'Adidas', 'Zara', 'H&M'];

    private array $materials = ['Cotton', 'Polyester', 'Leather', 'Stainless Steel', 'Plastic', 'Wood', 'Glass', 'Ceramic', 'Aluminum', 'Silk'];

    private array $deliveryOptions = [
        'Free delivery within Sanaa. Delivery within 24 hours.',
        'Standard delivery 2-5 business days. Express delivery available.',
        'Free shipping on orders over $25. Same-day delivery in some areas.',
        'Delivery within 3-7 business days. Track your order online.',
        'We deliver to your doorstep. Cash on delivery available.',
    ];

    private array $notesOptions = [
        'Available in multiple colors — contact shop for options.',
        'Limited stock remaining. Order now to avoid disappointment.',
        'This item comes with a gift box included. Perfect for gifting.',
        'Handcrafted with care. Each piece may vary slightly in appearance.',
        'Imported directly from the manufacturer. Authentic product guaranteed.',
        'Bulk purchase discounts available. Contact shop for wholesale pricing.',
        'Product images are for illustration only. Actual product may vary.',
        'Eco-friendly materials. 100% recyclable packaging.',
    ];

    public function run(): void
    {
        $catIds = ProductCategory::pluck('id', 'name');

        // Set shop logos for those missing
        Shop::whereNull('logo')->chunk(50, function ($shops) {
            foreach ($shops as $shop) {
                $shop->update(['logo' => 'shops/' . ($shop->iconname ?? 'shop') . '_logo.png']);
            }
        });

        // Update ALL existing products with rich detail fields
        Product::chunk(100, function ($products) {
            foreach ($products as $product) {
                $this->enrichProduct($product);
            }
        });

        $total = Product::count();
        echo "\n✅ ProductSeeder complete: {$total} products enriched with detail fields\n";
    }

    private function enrichProduct(Product $product): void
    {
        $data = [];

        if (empty($product->highlights)) {
            $data['highlights'] = $this->randomHighlights((bool) $product->warranty);
        } else {
            // Remove warranty highlight if product has no warranty
            if (!$product->warranty) {
                $highlights = array_values(array_filter($product->highlights, fn($h) => !str_contains(strtolower($h), 'warranty')));
                if (count($highlights) !== count($product->highlights)) {
                    $data['highlights'] = $highlights;
                }
            }
        }

        if (empty($product->specifications)) {
            $data['specifications'] = $this->randomSpecs((bool) $product->warranty);
        } else {
            // Clean up warranty spec if product has no warranty
            $specs = $product->specifications;
            if (!$product->warranty) {
                $specs = array_values(array_filter($specs, fn($s) => ($s['label'] ?? '') !== 'Warranty'));
            } else {
                $hasWarrantySpec = collect($specs)->first(fn($s) => ($s['label'] ?? '') === 'Warranty');
                if (!$hasWarrantySpec) {
                    $specs[] = ['label' => 'Warranty', 'value' => rand(0, 1) ? '1 Year' : '2 Years'];
                }
            }
            if ($specs !== $product->specifications) {
                $data['specifications'] = $specs;
            }
        }

        if (empty($product->tags)) {
            $data['tags'] = $this->randomTags();
        }

        if (empty($product->brand)) {
            $data['brand'] = $this->brands[array_rand($this->brands)];
        }

        if (empty($product->model_number)) {
            $prefix = $product->name ? strtoupper(substr($product->name, 0, 3)) : 'PRD';
            $data['model_number'] = $prefix . '-' . ($product->shop_id ?? 0) . '-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT);
        }

        if (empty($product->color)) {
            $colors = ['Black', 'White', 'Red', 'Blue', 'Green', 'Gold', 'Silver', 'Brown'];
            $data['color'] = $colors[array_rand($colors)];
        }

        if (empty($product->size)) {
            $sizes = ['S', 'M', 'L', 'XL', 'One Size', '10cm', '20cm', '30cm'];
            $data['size'] = $sizes[array_rand($sizes)];
        }

        if (empty($product->material)) {
            $data['material'] = $this->materials[array_rand($this->materials)];
        }

        if (empty($product->weight)) {
            $data['weight'] = round(0.1 + (rand(1, 500) / 100), 2);
        }

        if (empty($product->shipping_info)) {
            $data['shipping_info'] = 'Ships within 2-3 business days. Free shipping on orders over $50.';
        }

        if (empty($product->delivery_info)) {
            $data['delivery_info'] = $this->deliveryOptions[array_rand($this->deliveryOptions)];
        }

        if (empty($product->return_policy)) {
            $data['return_policy'] = '30-day return policy. Items must be unused and in original packaging.';
        }

        if (empty($product->notes)) {
            $data['notes'] = $this->notesOptions[array_rand($this->notesOptions)];
        }

        if (!empty($data)) {
            $product->update($data);
        }
    }

    private function randomHighlights(bool $hasWarranty = true): array
    {
        $pool = [
            'Premium quality materials',
            'Verified authentic product',
            'Eco-friendly packaging',
            'Fast & free delivery',
            'Best seller in category',
            'Top-rated by customers',
            'Easy returns within 30 days',
            'Energy efficient design',
            'Water resistant protection',
        ];
        if ($hasWarranty) {
            $pool[] = '1-year warranty included';
        }
        shuffle($pool);
        return array_slice($pool, 0, rand(3, 5));
    }

    private function randomSpecs(bool $hasWarranty = true): array
    {
        $colors = ['Black', 'White', 'Red', 'Blue'];
        $specs = [
            ['label' => 'Weight', 'value' => round(0.1 + (rand(1, 500) / 100), 2) . ' kg'],
            ['label' => 'Dimensions', 'value' => rand(5, 50) . ' × ' . rand(5, 50) . ' × ' . rand(2, 20) . ' cm'],
            ['label' => 'Country of Origin', 'value' => 'Yemen'],
            ['label' => 'Color', 'value' => $colors[array_rand($colors)]],
        ];
        $warrantySpec = ['label' => 'Warranty', 'value' => rand(0, 1) ? '1 Year' : '2 Years'];
        if ($hasWarranty) {
            $specs[] = $warrantySpec;
        }
        shuffle($specs);
        $selected = array_slice($specs, 0, rand(2, 4));
        if ($hasWarranty && !collect($selected)->first(fn($s) => ($s['label'] ?? '') === 'Warranty')) {
            $selected[array_rand($selected)] = $warrantySpec;
        }
        return $selected;
    }

    private function randomTags(): array
    {
        $pool = ['popular', 'trending', 'new-arrival', 'best-seller', 'sale', 'premium', 'limited-edition', 'eco-friendly', 'top-rated', 'clearance'];
        shuffle($pool);
        return array_slice($pool, 0, rand(2, 4));
    }
}
