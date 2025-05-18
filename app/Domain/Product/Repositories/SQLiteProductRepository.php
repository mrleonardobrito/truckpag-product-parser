<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\Product as DomainProduct;
use App\Models\SQLite\Product as EloquentProduct;

class SQLiteProductRepository implements ProductRepositoryInterface
{
    public function findByCode(int $code): DomainProduct
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

    public function updateByCode(int $code, array $data): DomainProduct
    {
        // Validação dos campos opcionais: se presentes, devem ser válidos
        $errors = [];
        if (array_key_exists('product_name', $data)) {
            if (!is_string($data['product_name']) || trim($data['product_name']) === '') {
                $errors['product_name'] = ['O nome do produto, se enviado, não pode ser vazio ou inválido.'];
            }
        }
        if (array_key_exists('quantity', $data)) {
            if (!is_string($data['quantity']) || trim($data['quantity']) === '') {
                $errors['quantity'] = ['A quantidade, se enviada, não pode ser vazia ou inválida.'];
            }
        }
        // Adicione outras validações conforme necessário

        if (!empty($errors)) {
            // Lança uma exceção HTTP 422
            abort(422, json_encode(['errors' => $errors]));
        }

        $product = EloquentProduct::where('code', $code)->firstOrFail();
        $product->update($data);
        return new DomainProduct($product->fresh()->toArray());
    }
}