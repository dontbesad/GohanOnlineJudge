<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Admin_List {

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

    }
    //第几页，每页多少记录
    public function execute() {

        $this->filter();

        $admin_list = Oj::get_admin_list();
        foreach ($admin_list as &$admin) {
            $user_role = Oj::get_user_role($admin['user_id']);
            $role_id_arr = array_column($user_role, 'role_id');
            $user_role_list = Oj::get_role_by_user($role_id_arr);

            $admin['role'] = array_column($user_role_list, 'name');

            $user = Oj::get_user_info_by_id($admin['user_id']);
            $admin['username'] = $user['username'];
        }
        $ret['list'] = $admin_list;
        return $ret;
    }

}
