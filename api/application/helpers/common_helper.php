<?php
/**
 * 获取指定数据库实例,默认oj数据库
 */
if (!function_exists('get_db')) {

    function get_db($db_name='oj') {
        static $CI;
        static $OJ_DB;

        if (!isset($CI)) {
            $CI = &get_instance();
        }
        if (!isset($OJ_DB)) {
            $OJ_DB = $CI->load->database($db_name, true);
        }

        return $OJ_DB;
    }
}

/**
 * 加密
 */
if (!function_exists('aes_base64_encrypt')) {

    function aes_base64_encrypt($str) {
        $method = 'AES-128-CBC';
        $key = 'gohanonlinejudge';
        $iv  = 'gohanonlinejudge';

        $res = openssl_encrypt($str, $method, $key, 0, $iv);
        return base64_encode($res);
    }
}

/**
 * 解密
 */
if (!function_exists('aes_base64_decrypt')) {

    function aes_base64_decrypt($str) {
        $res = base64_decode($str);
        if (!$res) {
            return false;
        }

        $method = 'AES-128-CBC';
        $key = 'gohanonlinejudge';
        $iv  = 'gohanonlinejudge';

        $res = openssl_decrypt($res, $method, $key, 0, $iv);
        if (!$res) {
            return false;
        }


        return $res;
    }
}
//保存登录信息,默认半小时
if (!function_exists('save_login')) {

    function save_login($user_data, $save_time=1800) {
        if (is_array($user_data)) {
            $user_data = json_encode($user_data);
        }
        $user_data = aes_base64_encrypt($user_data);
        $ret = setcookie('token', $user_data, time() + $save_time, '/');
    }
}
//解析登录信息
if (!function_exists('parse_login')) {

    function parse_login() {
        if (empty($_COOKIE['token'])) {
            return false;
        }
        $user_data = aes_base64_decrypt($_COOKIE['token']);
        if ($user_data == false) {
            return false;
        }
        return json_decode($user_data, true);
    }
}
