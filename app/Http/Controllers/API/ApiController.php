<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\ApiHandler;

class ApiController extends Controller
{
    public function __construct() {
        \App::singleton(ExceptionHandler::class, ApiHandler::class);
    }
}
