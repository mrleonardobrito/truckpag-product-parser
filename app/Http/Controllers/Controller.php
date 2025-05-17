<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="TruckPag Product Parser API",
 *     version="1.0.0",
 *     description="Documentação da API de produtos TruckPag"
 * )
 */ 

abstract class Controller extends BaseController
{
    use ValidatesRequests;
}
