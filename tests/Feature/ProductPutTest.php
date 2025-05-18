<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SQLite\Product;

class ProductPutTest extends TestCase
{
    use RefreshDatabase {
        refreshTestDatabase as baseRefreshTestDatabase;
    }

    protected function refreshTestDatabase()
    {
        if (app()->environment('testing')) {
            $this->baseRefreshTestDatabase();
        } else {
            throw new \Exception('Cannot refresh database in non-testing environment');
        }

    }

    public function test_update_product_by_code()
    {
        $product = Product::create([
            'code' => 12345,
            'status' => 'published',
            'imported_t' => now(),
            'url' => 'https://example.com',
            'creator' => 'admin',
            'created_t' => now(),
            'last_modified_t' => now(),
            'product_name' => 'Produto Teste',
            'quantity' => '1kg',
            'brands' => 'Marca Teste',
            'categories' => 'Categoria Teste',
            'labels' => 'Label Teste',
            'cities' => 'Cidade Teste',
            'purchase_places' => 'Lugar Teste',
            'stores' => 'Loja Teste',
            'ingredients_text' => 'ingrediente1, ingrediente2',
            'traces' => 'traco1, traco2',
            'serving_size' => '100g',
            'serving_quantity' => 100,
            'nutriscore_score' => 10,
            'nutriscore_grade' => 'A',
            'main_category' => 'Categoria Principal',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        $payload = [
            'product_name' => 'Produto Atualizado',
            'quantity' => '2kg',
        ];

        $response = $this->putJson("/api/products/{$product->code}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'product_name' => 'Produto Atualizado',
                'quantity' => '2kg',
            ]);
    }

    public function test_update_product_with_invalid_data()
    {
        $product = Product::create([
            'code' => 12345,
            'status' => 'published',
            'imported_t' => now(),
            'url' => 'https://example.com',
            'creator' => 'admin',
            'created_t' => now(),
            'last_modified_t' => now(),
            'product_name' => 'Produto Teste',
            'quantity' => '1kg',
            'brands' => 'Marca Teste',
            'categories' => 'Categoria Teste',
            'labels' => 'Label Teste',
            'cities' => 'Cidade Teste',
            'purchase_places' => 'Lugar Teste',
            'stores' => 'Loja Teste',
            'ingredients_text' => 'ingrediente1, ingrediente2',
            'traces' => 'traco1, traco2',
            'serving_size' => '100g',
            'serving_quantity' => 100,
            'nutriscore_score' => 10,
            'nutriscore_grade' => 'A',
            'main_category' => 'Categoria Principal',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        $payload = [
            'product_name' => '',
            'quantity' => null,
        ];

        $response = $this->putJson("/api/products/{$product->code}", $payload);

        $response->assertStatus(422);
    }

    public function test_update_nonexistent_product()
    {
        $payload = [
            'product_name' => 'Produto Atualizado',
            'quantity' => '2kg',
        ];

        $response = $this->putJson("/api/products/99999", $payload);

        $response->assertStatus(404);
    }

    public function test_update_product_with_all_fields()
    {
        $product = Product::create([
            'code' => 12345,
            'status' => 'published',
            'imported_t' => now(),
            'url' => 'https://example.com',
            'creator' => 'admin',
            'created_t' => now(),
            'last_modified_t' => now(),
            'product_name' => 'Produto Teste',
            'quantity' => '1kg',
            'brands' => 'Marca Teste',
            'categories' => 'Categoria Teste',
            'labels' => 'Label Teste',
            'cities' => 'Cidade Teste',
            'purchase_places' => 'Lugar Teste',
            'stores' => 'Loja Teste',
            'ingredients_text' => 'ingrediente1, ingrediente2',
            'traces' => 'traco1, traco2',
            'serving_size' => '100g',
            'serving_quantity' => 100,
            'nutriscore_score' => 10,
            'nutriscore_grade' => 'A',
            'main_category' => 'Categoria Principal',
            'image_url' => 'https://example.com/image.jpg',
        ]);

        $payload = [
            'product_name' => 'Produto Atualizado',
            'quantity' => '2kg',
            'brands' => 'Nova Marca',
            'categories' => 'Nova Categoria',
            'labels' => 'Novo Label',
            'cities' => 'Nova Cidade',
            'purchase_places' => 'Novo Lugar',
            'stores' => 'Nova Loja',
            'ingredients_text' => 'novo ingrediente1, novo ingrediente2',
            'traces' => 'novo traco1, novo traco2',
            'serving_size' => '200g',
            'serving_quantity' => 200,
            'nutriscore_score' => 20,
            'nutriscore_grade' => 'B',
            'main_category' => 'Nova Categoria Principal',
            'image_url' => 'https://example.com/nova-imagem.jpg',
        ];

        $response = $this->putJson("/api/products/{$product->code}", $payload);

        $response->assertStatus(200)
            ->assertJson($payload);
    }
}