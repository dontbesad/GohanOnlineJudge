<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Login {

    private function filter($post_data) {
        if (empty($post_data['username'])) {
            throw new Exception('用户名不能为空', 404);
        } else if (empty($post_data['password'])) {
            throw new Exception('密码不能为空', 404);
        } else if (!preg_match('/^\w+$/', $post_data['username'])) {
            throw new Exception('用户名格式错误', 404);
        } else if (!preg_match('/^\w+$/', $post_data['password'])) {
            throw new Exception('密码格式错误', 404);
        }

        $user_info = Oj::get_user_info($post_data['username']);
        if ( empty($user_info) ) {
            throw new Exception('用户名不存在', 404);
        } else if ( empty(Oj::get_user_info($post_data['username'], $post_data['password'])) ) {
            throw new Exception('密码错误', 404);
        }

        return $user_info;
    }

    public function execute($post_data) {
        $user = $this->filter($post_data);
        $save_data = ['user_id' => $user['user_id'], 'username' => $user['username']];
        save_login($save_data);
        return true;
    }

}
