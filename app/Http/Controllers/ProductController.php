<?php

namespace App\Http\Controllers;

use App\Domain\Product\ProductRepositoryInterface;

class ProductController extends Controller
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
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

    /**
     * @OA\Put(
     *     path="/products/{code}",
     *     summary="Atualiza um produto",
     *     tags={"Produtos"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Código do produto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string),
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="creator", type="string"),
     *             @OA\Property(property="product_name", type="string"),
     *             @OA\Property(property="quantity", type="string"),
     *             @OA\Property(property="brands", type="string"),
     *             @OA\Property(property="categories", type="string"),
     *             @OA\Property(property="labels", type="string"),
     *             @OA\Property(property="cities", type="string"),
     *             @OA\Property(property="purchase_places", type="string"),
     *             @OA\Property(property="stores", type="string"),
     *             @OA\Property(property="ingredients_text", type="string"),
     *             @OA\Property(property="traces", type="string"),
     *             @OA\Property(property="serving_size", type="string"),
     *             @OA\Property(property="serving_quantity", type="number"),
     *             @OA\Property(property="nutriscore_score", type="number"),
     *             @OA\Property(property="nutriscore_grade", type="string"),
     *             @OA\Property(property="main_category", type="string"),
     *             @OA\Property(property="image_url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="No content"
     *     )
     * )
     */
    public function update(int $code)
    {
        $data = request()->all();
        $product = $this->productRepository->updateByCode($code, $data);
        return response()->json($product);
    }
}