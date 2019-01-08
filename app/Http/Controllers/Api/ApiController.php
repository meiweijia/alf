<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Traits\ApiResponse;
use App\Traits\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    use ApiResponse, Paginator;

    /**
     * 参数检查
     *
     * @param Request $request
     * @param array $param
     * @throws InvalidRequestException
     */
    public function checkPar(Request $request, array $param)
    {
        $validator = Validator::make($request->all(), $param);
        if ($validator->fails()) {
            throw new InvalidRequestException($validator->errors(), '缺少参数');
        }
    }
}
