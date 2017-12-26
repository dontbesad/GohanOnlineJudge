<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Search_User {

    const DATA_LIST = [
        'username'
    ];

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        $post_data = json_decode(file_get_contents('php://input'), true);
        if (empty($post_data['username'])) {
            throw new Exception('用户名不能为空', 400);
        } else if (!preg_match('/^\w+$/', $post_data['username'])) {
            throw new Exception('用户名只能包含下划线,字母,数字,汉字', 400);
        }

        $user = Oj::get_user_info($post_data['username']);
        if (empty($user)) {
            throw new Exception('此用户不存在', 404);
        }
        return $user;
    }

    public function execute() {

        $user = $this->filter();

        $user_role = Oj::get_user_role($user['user_id']);
        if (!empty($user_role)) {
            $role_id_arr = array_column($user_role, 'role_id');
            $user_role_list = Oj::get_role_by_user($role_id_arr);

            $user['role'] = array_column($user_role_list, 'name');
        } else {
            $user['role'] = [];
        }

        return $user;
    }

}
