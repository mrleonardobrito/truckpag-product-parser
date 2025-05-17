<?php

namespace App\Infrastructure\Product;

use App\Domain\Product as DomainProduct;
use App\Models\Product as EloquentProduct;

class ProductRepository
{
    public function save(DomainProduct $product): EloquentProduct
    {
        return EloquentProduct::create($product->toArray());
    }

    public function findByCode(int $code): DomainProduct
    {
        $product = EloquentProduct::where('code', $code)->firstOrFail();
        return new DomainProduct($product->toArray());
    }

    public function findAllPaginated(int $page, int $perPage): array
    {
        return EloquentProduct::paginate($perPage, ['*'], 'page', $page)->map(function ($item) {
            return new DomainProduct($item->toArray());
        })->toArray();
    }
} 