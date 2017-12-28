<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Register {
    const KEY_LIST = [
        'username' => [
            'must' => 1,
            'preg' => '/^\w+$/'
        ],
        'nickname' => [
            'must' => 1,
            'preg' => '/^[(\x{4e00}-\x{9fa5})\w]+$/u'
        ],
        'password' => [
            'must' => 1,
            'preg' => '/^\w+$/'
        ],
        'password_again' => [
            'must' => 1,
            'preg' => '/^\w+$/'
        ],
        'school' => [
            'must' => 0,
            'preg' => '/^\w+$/'
        ],
        'email' => [
            'must' => 0,
            'preg' => '/^\w+@\w+\.\w+$/'
        ],
        'description' => [
            'must' => 0,
            'preg' => '/^[\w\s]+$/'
        ],
    ];

    private function filter($post_data) {
        $data = [];
        foreach (self::KEY_LIST as $key => $rule) {
            if (!empty($post_data[$key]) && !preg_match($rule['preg'], $post_data[$key])) {
                throw new Exception($key.'格式不正确', 403);
            } else if ($rule['must'] && empty($post_data[$key])) {
                throw new Exception($key.'不能为空', 403);
            } else if (!empty($post_data[$key])) {
                if ($key == 'password_again') {
                    if ($post_data['password'] != $post_data['password_again']) {
                        throw new Exception($key.'不相同', 403);
                    }
                    continue;
                }
                $data[$key] = $post_data[$key];
            }
        }
        if (!empty(Oj::get_user_info($data['username']))) {
            throw new Exception("用户名已存在", 403);
        }
        return $data;
    }

    public function execute($post_data) {
        $data = $this->filter($post_data);
        Oj::insert_user_info($data);
        return true;
    }

}
