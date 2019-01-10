<?php

namespace App\Http\Controllers\Api\V1;


use App\Facades\EasySms;
use App\Http\Controllers\Api\ApiController;
use App\Libraries\SMS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $this->checkPar($request, [
            'mobile_no' => 'required'
        ]);


        $mobile_no = $request->input('mobile_no');

        if (!$code = cache(SMS::MOBILE_CODE_KEY . '_' . $mobile_no)) {
            $code = generate_code(4);

            //TODO 这里稍微再封装一个SMS类
            $result = EasySms::send($mobile_no, [
                'content' => '您的验证码为: ' . $code,
                'template' => 'SMS_129070037',
                'data' => [
                    'code' => $code
                ],
            ]);
            Log::info('sms_code_result', $result);
            cache([SMS::MOBILE_CODE_KEY . '_' . $mobile_no => $code], Carbon::now()->addMinute(5));
        }
        return isset($result['aliyun']['result']['Code']) || $code ? $this->success($code) : $this->error(null);
    }

    /**
     *检测验证码
     *
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function checkCode(Request $request)
    {
        $this->checkPar($request, [
            'mobile_no' => 'required',
            'code' => 'required',
        ]);

        return Sms::checkCode($request->input('mobile_no'), $request->input('code')) ? $this->success(null) : $this->error(null);
    }
}