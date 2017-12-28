<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_List {

    private function filter($page, $size) {
        if (!isset($page) || !isset($size)
            || $page < 1 || $size < 1
            || !preg_match('/^\d+$/', $page) || !preg_match('/^\d+$/', $size)) {
            throw new Exception('页面不存在', 404);
        }
    }
    //第几页，每页多少记录
    public function execute($page, $size) {

        $this->filter($page, $size);

        $visible = check_admin() ? 0 : 1; //管理员可以看见不可见的题目
        $ret['list'] = Oj::get_problem_list_page(($page - 1)*$size, $size, $visible);

        $login_data = parse_login();
        $user_id = empty($login_data) ? 0 : $login_data['user_id'];

        foreach ($ret['list'] as &$problem) {
            if (empty($user_id)) {
                $problem['status'] = 0; //0未提交，-1错误, 1AC
                continue;
            }
            $solution_list = Oj::get_solution_by_contest(['result'], ['user_id' => $user_id, 'contest_id' => 0, 'problem_id' => $problem['problem_id']]);
            if (empty($solution_list)) {
                $problem['status'] = 0;
            } else if (in_array(1, array_column($solution_list, 'result'))) {
                $problem['status'] = 1;
            } else {
                $problem['status'] = -1;
            }
        }

        $ret['num']  = Oj::get_problem_num($visible);

        return $ret;
    }

}
