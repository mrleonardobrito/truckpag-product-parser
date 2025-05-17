<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Lista todos os produtos",
     *     tags={"Produtos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de produtos retornada com sucesso"
     *     )
     * )
     */
    public function index(){
        return Product::all();
    }
}