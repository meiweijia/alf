<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use App\Traits\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    use ApiResponse,Paginator;
}
