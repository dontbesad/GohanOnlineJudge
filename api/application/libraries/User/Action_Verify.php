<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Verify {

    private function filter() {

    }

    public function execute() {
        $login_data = parse_login();
        $ret['login'] = !empty($login_data);
        if ($ret['login']) {
            $ret['user_id']  = $login_data['user_id'];
            $ret['username'] = $login_data['username'];
            //这里可以进行将cookie更新
        }
        return $ret;
    }

}
