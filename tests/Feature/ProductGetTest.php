<?php

namespace Tests\Feature;

use App\Models\SQLite\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductGetTest extends TestCase
{
    use RefreshDatabase {
        refreshTestDatabase as baseRefreshTestDatabase;
    }

    protected function refreshTestDatabase()
    {
        if (app()->environment('testing')) {
            $this->baseRefreshTestDatabase();
        }else {
            throw new \Exception('Cannot refresh database in non-testing environment');
        }

    }

    public function test_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'status',
                        'url',
                        'creator',
                        'product_name',
                        'quantity',
                        'brands',
                        'categories',
                        'labels',
                        'cities',
                        'purchase_places',
                        'stores',
                        'ingredients_text',
                        'traces',
                        'serving_size',
                        'serving_quantity',
                        'nutriscore_score',
                        'nutriscore_grade',
                        'main_category',
                        'image_url',
                        'imported_t',
                        'created_t',
                        'last_modified_t'
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]);
    }

    public function test_can_show_specific_product()
    {
        // Arrange
        $product = Product::factory()->create();

        // Act
        $response = $this->getJson("/api/products/{$product->code}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'code' => $product->code,
                'product_name' => $product->product_name,
                'status' => $product->status,
                'url' => $product->url,
                'creator' => $product->creator,
                'quantity' => $product->quantity,
                'brands' => $product->brands,
                'categories' => $product->categories,
                'labels' => $product->labels,
                'cities' => $product->cities,
                'purchase_places' => $product->purchase_places,
                'stores' => $product->stores,
                'ingredients_text' => $product->ingredients_text,
                'traces' => $product->traces,
                'serving_size' => $product->serving_size,
                'serving_quantity' => $product->serving_quantity,
                'nutriscore_score' => $product->nutriscore_score,
                'nutriscore_grade' => $product->nutriscore_grade,
                'main_category' => $product->main_category,
                'image_url' => $product->image_url,
            ]);
    }

    public function test_returns_404_when_product_not_found()
    {
        // Act
        $response = $this->getJson('/products/999999');

        // Assert
        $response->assertStatus(404);
    }
}
