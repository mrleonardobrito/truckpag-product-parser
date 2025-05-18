<?php

namespace Tests\Feature;

use App\Models\SQLite\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductDeleteTest extends TestCase {
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

    public function test_delete_product()
    {
        $product = Product::factory()->create([
            'code' => '1234567890',
        ]);
        $response = $this->deleteJson('/api/products/1234567890');
        $response->assertStatus(204);
        $this->assertDatabaseHas('products', ['code' => '1234567890', 'status' => 'trash']);
    }

    public function test_delete_product_not_found()
    {
        $product = Product::factory()->create([
            'code' => '1234567890',
        ]);
        $product->delete();
        $response = $this->deleteJson('/api/products/1234567890');
        $response->assertStatus(404);
    }
}