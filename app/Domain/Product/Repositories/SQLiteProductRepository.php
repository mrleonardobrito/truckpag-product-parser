<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\ProductStatus;
use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\Product as DomainProduct;
use App\Models\SQLite\Product as EloquentProduct;

class SQLiteProductRepository implements ProductRepositoryInterface
{
    public function findByCode(string $code): DomainProduct
    {
        $product = EloquentProduct::where('code', $code)->firstOrFail();
        return new DomainProduct($product->toArray());
    }

    public function findAllPaginated(int $page, int $perPage): array
    {
        $paginator = EloquentProduct::paginate($perPage, ['*'], 'page', $page);

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

    public function updateByCode(string $code, array $data): DomainProduct
    {
        $product = EloquentProduct::where('code', $code)->firstOrFail();
        $product->update($data);
        return new DomainProduct($product->fresh()->toArray());
    }

    public function deleteByCode(string $code): void
    {
        EloquentProduct::where('code', $code)->update(['status' => ProductStatus::TRASH->value]);
    }

    public function updateOrCreate(DomainProduct $product): DomainProduct
    {
        $product = EloquentProduct::updateOrCreate(['code' => $product->code], $product->toArray());
        return new DomainProduct($product->toArray());
    }
}