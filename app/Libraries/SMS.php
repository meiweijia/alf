<?php

namespace App\Libraries;


class SMS
{
    const MOBILE_CODE_KEY = 'MOBILE_CODE';

    /**
     * 检查验证码是否正确
     *
     * @param $no
     * @param $code
     * @return bool
     * @throws \Exception
     */
    public static function checkCode($no, $code)
    {
        return cache(SMS::MOBILE_CODE_KEY . '_' . $no) == $code;
    }
}