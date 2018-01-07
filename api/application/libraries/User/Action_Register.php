<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Register {
    const KEY_LIST = [
        'username' => [
            'must' => 1,
            'preg' => '/^\w+$/',
            'hint' => '用户名只能包含数字英文下划线'
        ],
        'nickname' => [
            'must' => 1,
            'preg' => '/^[(\x{4e00}-\x{9fa5})\w]+$/u',
            'hint' => '昵称只能包含中文数字英文下划线'
        ],
        'password' => [
            'must' => 1,
            'preg' => '/^\w+$/',
            'hint' => '密码只能包含数字英文下划线'
        ],
        'password_again' => [
            'must' => 1,
            'preg' => '/^\w+$/',
            'hint' => '重复输入的密码只能包含数字英文下划线'
        ],
        'school' => [
            'must' => 0,
            'preg' => '/^[(\x{4e00}-\x{9fa5})\w]+$/u',
            'hint' => '学校只能包含中文数字英文下划线'
        ],
        'email' => [
            'must' => 0,
            'preg' => '/^\w+@\w+\.\w+$/',
            'hint' => '邮箱格式不正确'
        ],
        'description' => [
            'must' => 0,
            'preg' => '/^[(\x{4e00}-\x{9fa5})\w]+$/u',
            'hint' => '描述只能包含中文数字英文下划线'
        ],
    ];

    private function filter($post_data) {
        $data = [];
        foreach (self::KEY_LIST as $key => $rule) {
            if (!empty($post_data[$key]) && !preg_match($rule['preg'], $post_data[$key])) {
                throw new Exception($rule['hint'], 403);
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
