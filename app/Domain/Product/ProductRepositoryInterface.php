<?php

namespace App\Domain\Product;

interface ProductRepositoryInterface
{
    public function findByCode(int $code): Product;
    public function findAllPaginated(int $page, int $perPage): array;
    public function updateByCode(int $code, array $data): Product;
} 