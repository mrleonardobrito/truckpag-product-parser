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

    protected $casts = [
        'imported_t' => 'datetime',
        'created_t' => 'datetime',
        'last_modified_t' => 'datetime'
    ];

    

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status,
            'imported_t' => $this->imported_t,
            'url' => $this->url,
            'creator' => $this->creator,
            'created_t' => $this->created_t,
            'last_modified_t' => $this->last_modified_t,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'brands' => $this->brands,
            'categories' => $this->categories,
            'labels' => $this->labels,
            'cities' => $this->cities,
            'purchase_places' => $this->purchase_places,
            'stores' => $this->stores,
            'ingredients_text' => $this->ingredients_text,
            'traces' => $this->traces,
            'serving_size' => $this->serving_size,
            'serving_quantity' => $this->serving_quantity,
            'nutriscore_score' => $this->nutriscore_score,
            'nutriscore_grade' => $this->nutriscore_grade,
            'main_category' => $this->main_category,
            'image_url' => $this->image_url,
        ];
    }
}