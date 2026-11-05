<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ],
        );

        $categories = collect([
            'Shoes',
            'Clothing',
            'Accessories',
        ])->mapWithKeys(fn (string $name) => [
            $name => Category::query()->updateOrCreate(['name' => $name]),
        ]);

        $brands = collect([
            'Nike',
            'Adidas',
            'Puma',
        ])->mapWithKeys(fn (string $name) => [
            $name => Brand::query()->updateOrCreate(['name' => $name]),
        ]);

        $products = [
            [
                'category' => 'Shoes',
                'brand' => 'Nike',
                'title' => 'Nike Air Max',
                'price' => '129.99',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff',
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772',
                ],
                'variants' => [
                    ['size' => '40', 'color' => 'Black', 'stock' => 12],
                    ['size' => '41', 'color' => 'Red', 'stock' => 8],
                ],
            ],
            [
                'category' => 'Shoes',
                'brand' => 'Adidas',
                'title' => 'Adidas Ultraboost',
                'price' => '149.99',
                'images' => [
                    'https://images.unsplash.com/photo-1539185441755-769473a23570',
                ],
                'variants' => [
                    ['size' => '42', 'color' => 'White', 'stock' => 10],
                    ['size' => '43', 'color' => 'Grey', 'stock' => 6],
                ],
            ],
            [
                'category' => 'Clothing',
                'brand' => 'Puma',
                'title' => 'Puma Essentials Hoodie',
                'price' => '69.99',
                'images' => [
                    'https://images.unsplash.com/photo-1556821840-3a63f95609a7',
                ],
                'variants' => [
                    ['size' => 'M', 'color' => 'Black', 'stock' => 15],
                    ['size' => 'L', 'color' => 'Grey', 'stock' => 9],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::query()->updateOrCreate(
                ['title' => $productData['title']],
                [
                    'category_id' => $categories[$productData['category']]->id,
                    'brand_id' => $brands[$productData['brand']]->id,
                    'price' => $productData['price'],
                ],
            );

            $product->images()->delete();
            $product->variants()->delete();

            foreach ($productData['images'] as $imageUrl) {
                $product->images()->create(['image_url' => $imageUrl]);
            }

            foreach ($productData['variants'] as $variant) {
                $product->variants()->create($variant);
            }
        }
    }
}
