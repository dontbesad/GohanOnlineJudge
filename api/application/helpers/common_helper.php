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
 * 获取redis实例
 */
if (!function_exists('get_redis')) {

    function get_redis() {
        static $redis;

        if (!isset($redis)) {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
        }

        return $redis;
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

//判断用户是否拥有admin权限
if (!function_exists('check_admin')) {

    function check_admin($user_id=0) {
        if (empty($user_id)) {
            $login_info = parse_login();
            if (empty($login_info)) {
                return false;
            }
            $user_id = $login_info['user_id'];
        }

        $user_role = Oj::get_user_role($user_id);
        return !empty($user_role);
    }
}

//查看用户在某个api下有没有权限限制
if (!function_exists('check_permission')) {

    function check_permission($user_id=0) {
        $class=strtolower(get_instance()->router->fetch_class());
        $method=strtolower(get_instance()->router->fetch_method());
        if (empty($user_id)) {
            $login_info = parse_login();
            if (empty($login_info)) {
                return false;
            }
            $user_id = $login_info['user_id'];
        }
        //用户没有角色
        $user_role = Oj::get_user_role($user_id);
        if (empty($user_role)) {
            return false;
        }
        //此接口没有在记录中
        $rule_id = Oj::get_rule_id($class, $method);
        if (empty($rule_id)) {
            return true;
        }
        //此接口记录对应的角色列表
        $role_list = Oj::get_role_list_by_rule($rule_id);
        if (empty($role_list)) {
            return false;
        }
        $role_id_list = array_column($role_list, 'role_id');

        foreach ($user_role as $role) {
            if (in_array($role['role_id'], $role_id_list)) {
                return true;
            }
        }
        return false;

    }
}
