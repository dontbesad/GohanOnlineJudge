<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Role_List {

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

    }

    public function execute() {

        $this->filter();

        $role_list = Oj::get_role_list();
        
        $ret['list'] = $role_list;
        return $ret;
    }

}
