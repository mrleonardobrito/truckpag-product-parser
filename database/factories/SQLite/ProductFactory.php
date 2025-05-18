<?php

namespace Database\Factories\SQLite;

use App\Models\SQLite\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->numerify('##########'),
            'status' => $this->faker->randomElement(['draft', 'trash', 'published']),
            'url' => $this->faker->url,
            'creator' => $this->faker->name,
            'product_name' => $this->faker->words(3, true),
            'quantity' => $this->faker->randomNumber(3) . 'g',
            'brands' => $this->faker->company,
            'categories' => $this->faker->words(3, true),
            'labels' => $this->faker->words(2, true),
            'cities' => $this->faker->city,
            'purchase_places' => $this->faker->company,
            'stores' => $this->faker->company,
            'ingredients_text' => $this->faker->sentence,
            'traces' => $this->faker->words(2, true),
            'serving_size' => $this->faker->randomNumber(3) . 'g',
            'serving_quantity' => $this->faker->randomFloat(2, 0, 100),
            'nutriscore_score' => $this->faker->numberBetween(0, 100),
            'nutriscore_grade' => $this->faker->randomElement(['a', 'b', 'c', 'd', 'e']),
            'main_category' => $this->faker->word,
            'image_url' => $this->faker->imageUrl,
        ];
    }
}