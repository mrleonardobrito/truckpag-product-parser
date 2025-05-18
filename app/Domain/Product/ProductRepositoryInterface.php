<?php

namespace App\Domain\Product;

interface ProductRepositoryInterface
{
    public function findByCode(string $code): Product;
    public function findAllPaginated(int $page, int $perPage): array;
    public function updateByCode(string $code, array $data): Product;
    public function deleteByCode(string $code): void;
}