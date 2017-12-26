<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Delete {

    private function filter($contest_id) {
        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('参数不正确', 400);
        } else if (!Oj::get_contest($contest_id)) {
            throw new Exception('比赛不存在', 404);
        }

    }

    public function execute($contest_id) {
        $this->filter($contest_id);

        get_db()->trans_start(); //start

        Oj::delete_contest($contest_id);
        Oj::delete_contest_problem($contest_id);
        Oj::delete_contest_user($contest_id);
        Oj::delete_contest_solution($contest_id);

        get_db()->trans_complete(); //end...

        if (get_db()->trans_status() === false) {

            throw new Exception('数据库删除失败', 500);
        }

        return true;
    }


}
