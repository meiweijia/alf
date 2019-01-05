<?php

if (!function_exists('generate_code')) {
    /**
     *随机获取指定位数的数字
     *
     * @param int $length
     * @return int
     */
    function generate_code($length = 6)
    {
        return mt_rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }
}