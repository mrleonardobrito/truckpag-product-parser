<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'code' => '123456789',
                'status' => 'published',
                'imported_t' => now(),
                'url' => 'https://exemplo.com/produto1',
                'creator' => 'admin',
                'created_t' => now(),
                'last_modified_t' => now(),
                'product_name' => 'Arroz Integral',
                'quantity' => '1kg',
                'brands' => 'Marca A',
                'categories' => 'Alimentos, Grãos',
                'labels' => 'Orgânico, Sem Glúten',
                'cities' => 'São Paulo',
                'purchase_places' => 'Supermercado X',
                'stores' => 'Loja Principal',
                'ingredients_text' => 'Arroz integral, água',
                'traces' => 'Pode conter traços de soja',
                'serving_size' => '100g',
                'serving_quantity' => 1,
                'nutriscore_score' => 80,
                'nutriscore_grade' => 'A',
                'main_category' => 'Alimentos',
                'image_url' => 'https://exemplo.com/imagem1.jpg',
            ],
            [
                'code' => '987654321',
                'status' => 'published',
                'imported_t' => now(),
                'url' => 'https://exemplo.com/produto2',
                'creator' => 'admin',
                'created_t' => now(),
                'last_modified_t' => now(),
                'product_name' => 'Feijão Carioca',
                'quantity' => '1kg',
                'brands' => 'Marca B',
                'categories' => 'Alimentos, Grãos',
                'labels' => 'Tradicional',
                'cities' => 'Rio de Janeiro',
                'purchase_places' => 'Supermercado Y',
                'stores' => 'Loja Secundária',
                'ingredients_text' => 'Feijão carioca',
                'traces' => 'Pode conter traços de soja',
                'serving_size' => '100g',
                'serving_quantity' => 1,
                'nutriscore_score' => 75,
                'nutriscore_grade' => 'B',
                'main_category' => 'Alimentos',
                'image_url' => 'https://exemplo.com/imagem2.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 