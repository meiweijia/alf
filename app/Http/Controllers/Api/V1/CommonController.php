<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\ApiController;
use App\Libraries\SMS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class CommonController extends ApiController
{
    /**
     * 获取短信验证码
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function getCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->error(['error' => $validator->errors()]);
        }
        $code = generate_code(4);
        return cache([SMS::MOBILE_CODE_KEY . $request->input('mobile_no') => $code], Carbon::now()->addMinute(10)) ? $this->success(null) : $this->error(null);
    }
}