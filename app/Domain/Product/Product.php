<?php

namespace App\Domain\Product;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Product
{
    public string $code;
    public string $product_name;
    public ProductStatus $status;
    public int $imported_t;

    public ?string $url = null;
    public ?string $creator = null;
    public ?int $created_t = null;
    public ?int $last_modified_t = null;
    public ?string $quantity = null;
    public ?string $brands = null;
    public ?string $categories = null;
    public ?string $labels = null;
    public ?string $cities = null;
    public ?string $purchase_places = null;
    public ?string $stores = null;
    public ?string $ingredients_text = null;
    public ?string $traces = null;
    public ?string $serving_size = null;
    public ?float $serving_quantity = null;
    public ?int $nutriscore_score = null;
    public ?string $nutriscore_grade = null;
    public ?string $main_category = null;
    public ?string $image_url = null;

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
            if (array_key_exists($field, $attributes) && $attributes[$field] !== null) {
                if (!is_string($attributes[$field])) {
                    $errors[$field][] = "$label, if provided, cannot be empty or invalid.";
                }
            }
        }
        $numericFields = [
            'serving_quantity' => 'Serving quantity',
            'nutriscore_score' => 'Nutriscore score',
        ];
        foreach ($numericFields as $field => $label) {
            if (array_key_exists($field, $attributes) && $attributes[$field] !== null) {
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
            if (array_key_exists($field, $attributes) && $attributes[$field] !== null) {
                try {
                    $date = Carbon::createFromTimestamp($attributes[$field]);
                    $attributes[$field] = (int) $date->timestamp;
                } catch (\Exception $e) {
                    $errors[$field][] = "$label, if provided, must be a valid date.";
                }
            }
        }
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
        $this->code = $attributes['code'];
        $this->status = ProductStatus::from($attributes['status']);
        $this->imported_t = (int) $attributes['imported_t'];
        $this->url = $attributes['url'];
        $this->creator = $attributes['creator'];
        $this->created_t = (int) $attributes['created_t'];
        $this->last_modified_t = (int) $attributes['last_modified_t'];
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
            'status' => $this->status->value,
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
