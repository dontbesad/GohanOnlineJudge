<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Admin_Grant {

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        $post_data = json_decode(file_get_contents('php://input'), true);
        if (empty($post_data)) {
            throw new Exception('数据传输有误', 400);
        } else if (empty($post_data['user_list'])) {
            throw new Exception('请先选中用户', 400);
        }

        foreach ($post_data['user_list'] as $user_id) {
            if (empty(Oj::get_user_info_by_id($user_id))) {
                throw new Exception('选中的用户不存在', 400);
            }
        }
        if (!empty($post_data['role_list']) && empty(Oj::get_role_by_user($post_data['role_list']))) {
            throw new Exception('选中的管理角色不存在', 400);
        }

        return $post_data;

    }

    public function execute() {

        $data = $this->filter();

        get_db()->trans_start();
        foreach ($data['user_list'] as $user_id) {
            Oj::delete_user_role($user_id);
            $insert_data = [];
            foreach ($data['role_list'] as $role_id) {
                $insert_data[] = ['user_id' => $user_id, 'role_id' => $role_id];
            }
            Oj::insert_user_role($insert_data);
        }
        get_db()->trans_complete();

        if (get_db()->trans_status() === false) {
            throw new Exception('数据库执行失败', 500);
        }

        return true;
    }

}
