<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\Product as DomainProduct;
use App\Models\Mongo\Product as MongoProduct;

class MongoProductRepository implements ProductRepositoryInterface
{
    public function findByCode(int $code): DomainProduct
    {
        $product = MongoProduct::where('code', $code)->firstOrFail();
        return new DomainProduct($product->toArray());
    }

    public function findAllPaginated(int $page, int $perPage): array
    {
        $paginator = MongoProduct::paginate($perPage, ['*'], 'page', $page);
        return [
            'data' => $paginator->map(function ($item) {
                return new DomainProduct($item->toArray());
            })->toArray(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    public function updateByCode(int $code, array $data): DomainProduct
    {
        $product = MongoProduct::where('code', $code)->firstOrFail();
        $product->update($data);
        return new DomainProduct($product->fresh()->toArray());
    }
}