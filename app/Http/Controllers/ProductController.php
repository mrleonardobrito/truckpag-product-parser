<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Infrastructure\Product\ProductRepository;
class ProductController extends Controller
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Lista todos os produtos",
     *     tags={"Produtos"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Quantidade de itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de produtos retornada com sucesso"
     *     )
     * )
     */
    public function index(){
        $perPage = request('per_page', 100);
        $page = request('page', 1);
        $products = $this->productRepository->findAllPaginated($page, $perPage);
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/products/{code}",
     *     summary="Exibe um produto específico",
     *     tags={"Produtos"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Código do produto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto retornado com sucesso"
     *     )
     * )
     */
    public function show(int $code){
        $product = $this->productRepository->findByCode($code);
        return response()->json($product);
    }
}