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
                'code' => 20221127,
                'status' => 'published',
                'imported_t' => '2023-01-15T10:30:00Z',
                'url' => 'https://world.openfoodfacts.org/product/20221127',
                'creator' => 'admin',
                'created_t' => 1673778600,
                'last_modified_t' => 1673778600,
                'product_name' => 'Pão de Queijo Congelado',
                'quantity' => '500g (20 unidades)',
                'brands' => 'Forno de Minas',
                'categories' => 'Alimentos congelados, Lanches, Pães',
                'labels' => 'Sem glúten, Contém lactose',
                'cities' => 'Belo Horizonte',
                'purchase_places' => 'São Paulo,Brasil',
                'stores' => 'Supermercado Extra',
                'ingredients_text' => 'polvilho azedo, queijo minas padrão, leite integral, ovos, óleo de soja, sal',
                'traces' => 'Leite,Ovos,Soja',
                'serving_size' => '25g',
                'serving_quantity' => 25,
                'nutriscore_score' => 12,
                'nutriscore_grade' => 'c',
                'main_category' => 'en:frozen-bread',
                'image_url' => 'https://exemplo.com/pao-queijo.jpg'
            ],
            [
                'code' => 20221128,
                'status' => 'published',
                'imported_t' => '2023-02-20T14:15:00Z',
                'url' => 'https://world.openfoodfacts.org/product/20221128',
                'creator' => 'admin',
                'created_t' => 1676898900,
                'last_modified_t' => 1676898900,
                'product_name' => 'Suco de Laranja Integral',
                'quantity' => '1L',
                'brands' => 'Natural One',
                'categories' => 'Bebidas, Sucos, Sucos de frutas',
                'labels' => 'Sem adição de açúcar, 100% natural',
                'cities' => 'Campinas',
                'purchase_places' => 'Rio de Janeiro,Brasil',
                'stores' => 'Supermercado Pão de Açúcar',
                'ingredients_text' => 'suco de laranja integral',
                'traces' => '',
                'serving_size' => '200ml',
                'serving_quantity' => 200,
                'nutriscore_score' => 85,
                'nutriscore_grade' => 'a',
                'main_category' => 'en:orange-juices',
                'image_url' => 'https://exemplo.com/suco-laranja.jpg'
            ],
            [
                'code' => 20221129,
                'status' => 'published',
                'imported_t' => '2023-03-10T09:45:00Z',
                'url' => 'https://world.openfoodfacts.org/product/20221129',
                'creator' => 'admin',
                'created_t' => 1678437900,
                'last_modified_t' => 1678437900,
                'product_name' => 'Chocolate Amargo 70%',
                'quantity' => '100g',
                'brands' => 'Cacau Show',
                'categories' => 'Doces, Chocolates, Chocolates amargos',
                'labels' => 'Sem lactose, Sem glúten',
                'cities' => 'São Paulo',
                'purchase_places' => 'São Paulo,Brasil',
                'stores' => 'Cacau Show',
                'ingredients_text' => 'massa de cacau, açúcar, manteiga de cacau, emulsificante lecitina de soja, aroma natural de baunilha',
                'traces' => 'Soja',
                'serving_size' => '25g',
                'serving_quantity' => 25,
                'nutriscore_score' => 8,
                'nutriscore_grade' => 'c',
                'main_category' => 'en:dark-chocolates',
                'image_url' => 'https://exemplo.com/chocolate-amargo.jpg'
            ],
            [
                'code' => 20221130,
                'status' => 'published',
                'imported_t' => '2023-04-05T11:20:00Z',
                'url' => 'https://world.openfoodfacts.org/product/20221130',
                'creator' => 'admin',
                'created_t' => 1680686400,
                'last_modified_t' => 1680686400,
                'product_name' => 'Arroz Integral',
                'quantity' => '1kg',
                'brands' => 'Camil',
                'categories' => 'Alimentos, Grãos, Arroz',
                'labels' => 'Integral, Sem glúten',
                'cities' => 'São Paulo',
                'purchase_places' => 'São Paulo,Brasil',
                'stores' => 'Supermercado Carrefour',
                'ingredients_text' => 'arroz integral',
                'traces' => '',
                'serving_size' => '100g',
                'serving_quantity' => 100,
                'nutriscore_score' => 90,
                'nutriscore_grade' => 'a',
                'main_category' => 'en:rice',
                'image_url' => 'https://exemplo.com/arroz-integral.jpg'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 