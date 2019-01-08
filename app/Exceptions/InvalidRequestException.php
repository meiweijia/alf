<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class InvalidRequestException extends Exception
{
    use ApiResponse;

    private $data;

    public function __construct($data = null, string $message = "", int $code = 400)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->error($this->data, $this->message, '缺少参数');
        }

        return view('pages.error', ['msg' => $this->message]);
    }
}