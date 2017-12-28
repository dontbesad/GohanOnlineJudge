<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Info {

    private function filter($problem_id) {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        if (empty($problem_id)) {
            throw new Exception('题目编号不能为空', 400);
        } else if (!preg_match('/^\d+$/', $problem_id)) {
            throw new Exception('题目号格式不正确', 400);
        }

        $problem = Oj::get_problem($problem_id, 0);
        if (empty($problem)) {
            throw new Exception('题目不存在', 404);
        }

        return $problem;
    }
    
    public function execute($problem_id) {

        $problem = $this->filter($problem_id);

        return $problem;
    }

}
