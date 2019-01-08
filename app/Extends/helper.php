<?php

if (!function_exists('generate_code')) {
    /**
     * 随机获取指定位数的数字
     *
     * @param int $length
     * @return int
     */
    function generate_code($length = 6)
    {
        return mt_rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }
}

if (!function_exists('week_day_map')) {
    /**
     * 获取一周内 周几跟日期的映射
     *
     * @return array
     */
    function week_day_map()
    {
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $day = date("Y-m-d", strtotime("+$i day"));
            $data[date('w', strtotime($day))] = $day;
        }
        return $data;
    }
}