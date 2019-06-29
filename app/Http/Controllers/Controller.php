<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Class Controller
 * @package App\Http\Controllers
 *
 * @SWG\Swagger(
 *     basePath="/api/",
 *     host="localhost:8000",
 *     schemes={"http", "https"},
 *     @SWG\Info(
 *         version="1.0",
 *         title="KB-KISA GRETA API",
 *         @SWG\Contact(name="Mingeun Kim", url="https://mingeun.com"),
 *     ),
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
