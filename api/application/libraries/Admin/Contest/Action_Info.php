<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Info {

    private function filter($contest_id) {
        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        $contest = Oj::get_contest($contest_id);
        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('参数不正确', 400);
        } else if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        }

        return $contest;
    }

    public function execute($contest_id) {
        $contest = $this->filter($contest_id);

        $contest_problem = Oj::get_problem_list_by_contest($contest_id);

        $problem_list = array_column($contest_problem, 'problem_id');
        $problem_list = implode(',',$problem_list);

        $contest['problem_list'] = $problem_list;
        return $contest;
    }


}
