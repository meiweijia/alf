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

if (!function_exists('week_map')) {
    /**
     * 星期的映射
     *
     * @return array
     */
    function week_map($v)
    {
        return week_arr()[$v];
    }
}

if (!function_exists('week_arr')) {
    /**
     * 星期的映射
     *
     * @return array
     */
    function week_arr()
    {
        return [
            '周日',
            '周一',
            '周二',
            '周三',
            '周四',
            '周五',
            '周六',
        ];
    }
}

if (!function_exists('time_map')) {
    /**
     * 可预定时间映射
     *
     * @return array
     */
    function time_map($v)
    {
        return time_arr()[$v];
    }
}

if (!function_exists('time_arr')) {
    /**
     * 可预定时间数组
     *
     * @return array
     */
    function time_arr()
    {
        return [
            8 => '08:00 - 09:00',
            9 => '09:00 - 10:00',
            10 => '10:00 - 11:00',
            11 => '11:00 - 12:00',
            12 => '12:00 - 13:00',
            13 => '13:00 - 14:00',
            14 => '14:00 - 15:00',
            15 => '15:00 - 16:00',
            16 => '16:00 - 17:00',
            17 => '17:00 - 18:00',
            18 => '18:00 - 19:00',
            19 => '19:00 - 20:00',
            20 => '20:00 - 21:00',
            21 => '21:00 - 22:00',
            22 => '22:00 - 23:00',
            23 => '23:00 - 00:00',
        ];
    }
}