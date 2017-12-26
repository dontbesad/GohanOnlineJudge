<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Verify {

    private function filter() {

    }

    public function execute() {
        $login_data = parse_login();
        $ret['login'] = !empty($login_data);
        $ret['admin'] = false;
        if ($ret['login']) {
            $ret['user_id']  = $login_data['user_id'];
            $ret['username'] = $login_data['username'];
            //这里可以进行将cookie更新
            $ret['admin'] = check_admin($login_data['user_id']);
            if ($ret['admin']) {
                $user_role = Oj::get_user_role($ret['user_id']);
                $ret['admin_power'] = count($user_role);
            }
        }
        return $ret;
    }

}
