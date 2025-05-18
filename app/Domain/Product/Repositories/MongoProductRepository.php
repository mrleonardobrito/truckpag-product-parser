<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\ProductStatus;
use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\Product as DomainProduct;
use App\Models\Mongo\Product as MongoProduct;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MongoProductRepository implements ProductRepositoryInterface
{
    public function findByCode(string $code): DomainProduct
    {
        $product = MongoProduct::where('code', $code)->firstOrFail();
        return new DomainProduct($product->toArray());
    }

    public function findAllPaginated(int $page, int $perPage): array
    {
        $paginator = MongoProduct::where('status', '!=', ProductStatus::TRASH->value)->paginate($perPage, ['*'], 'page', $page);
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
        $product = MongoProduct::where('code', $code)->where('status', '!=', ProductStatus::TRASH->value)->first();
        if ($product) {
            $product->update($data);
            return new DomainProduct($product->fresh()->toArray());
        } else {
            throw new ModelNotFoundException('Product not found');
        }
    }

    public function deleteByCode(string $code): void
    {
        $product = MongoProduct::where('code', $code)->where('status', '!=', ProductStatus::TRASH->value)->first();
        if ($product) {
            $product->update(['status' => ProductStatus::TRASH->value]);
        } else {
            throw new ModelNotFoundException('Product not found');
        }
    }

    public function updateOrCreate(DomainProduct $product): DomainProduct
    {
        $product = MongoProduct::updateOrCreate(['code' => $product->code], $product->toArray());
        return new DomainProduct($product->toArray());
    }
}