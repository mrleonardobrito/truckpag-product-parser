<?php

namespace App\Domain\Product;

use DateTime;
use Illuminate\Validation\ValidationException;

class Product
{
    public int|string $code;
    public string $status;
    public DateTime $imported_t;
    public string $url;
    public string $creator;
    public DateTime $created_t;
    public DateTime $last_modified_t;
    public string $product_name;
    public string $quantity;
    public string $brands;
    public string $categories;
    public string $labels;
    public string $cities;
    public string $purchase_places;
    public string $stores;
    public string $ingredients_text;
    public string $traces;
    public string $serving_size;
    public float $serving_quantity;
    public float $nutriscore_score;
    public string $nutriscore_grade;
    public string $main_category;
    public string $image_url;

    public function __construct(array $attributes)
    {
        $errors = [];
        $stringFields = [
            'product_name' => 'Product name',
            'quantity' => 'Quantity',
            'nutriscore_grade' => 'Nutriscore grade',
            'main_category' => 'Main category',
        ];
        foreach ($stringFields as $field => $label) {
            if (array_key_exists($field, $attributes)) {
                if (!is_string($attributes[$field]) || trim($attributes[$field]) === '') {
                    $errors[$field][] = "$label, if provided, cannot be empty or invalid.";
                }
            }
        }
        $numericFields = [
            'serving_quantity' => 'Serving quantity',
            'nutriscore_score' => 'Nutriscore score',
        ];
        foreach ($numericFields as $field => $label) {
            if (array_key_exists($field, $attributes)) {
                if (!is_numeric($attributes[$field])) {
                    $errors[$field][] = "$label, if provided, must be numeric.";
                }
            }
        }
        $dateFields = [
            'imported_t' => 'Import date',
            'created_t' => 'Creation date',
            'last_modified_t' => 'Modification date',
        ];
        foreach ($dateFields as $field => $label) {
            if (array_key_exists($field, $attributes)) {
                try {
                    new \DateTime($attributes[$field]);
                } catch (\Exception $e) {
                    $errors[$field][] = "$label, if provided, must be a valid date.";
                }
            }
        }
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
        $this->code = $attributes['code'];
        $this->status = $attributes['status'];
        $this->imported_t = new DateTime($attributes['imported_t']);
        $this->url = $attributes['url'];
        $this->creator = $attributes['creator'];
        $this->created_t = new DateTime($attributes['created_t']);
        $this->last_modified_t = new DateTime($attributes['last_modified_t']);
        $this->product_name = $attributes['product_name'];
        $this->quantity = $attributes['quantity'];
        $this->brands = $attributes['brands'];
        $this->categories = $attributes['categories'];
        $this->labels = $attributes['labels'];
        $this->cities = $attributes['cities'];
        $this->purchase_places = $attributes['purchase_places'];
        $this->stores = $attributes['stores'];
        $this->ingredients_text = $attributes['ingredients_text'];
        $this->traces = $attributes['traces'];
        $this->serving_size = $attributes['serving_size'];
        $this->serving_quantity = $attributes['serving_quantity'];
        $this->nutriscore_score = (int) $attributes['nutriscore_score'];
        $this->nutriscore_grade = $attributes['nutriscore_grade'];
        $this->main_category = $attributes['main_category'];
        $this->image_url = $attributes['image_url'];
    }

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
