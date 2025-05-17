<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model {
    protected $connection = 'mongodb';
    protected $collection = 'products';
    protected $fillable = [
        'code',
        'status',
        'imported_t',
        'url',
        'creator',
        'created_t',
        'last_modified_t',
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
    ];

    protected function casts() {
        return [
            'imported_t' => 'datetime',
            'created_t' => 'datetime',
            'last_modified_t' => 'datetime',
        ];
    }
}