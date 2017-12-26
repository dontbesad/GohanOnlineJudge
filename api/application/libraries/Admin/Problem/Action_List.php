<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_List {

    private function filter($page, $size) {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        if (!isset($page) || !isset($size)
            || $page < 1 || $size < 1
            || !preg_match('/^\d+$/', $page) || !preg_match('/^\d+$/', $size)) {
            throw new Exception('页面不存在', 404);
        }
    }
    //第几页，每页多少记录
    public function execute($page, $size) {

        $this->filter($page, $size);
        $ret['list'] = Oj::get_problem_list_page(($page - 1)*$size, $size, 0);
        $ret['num']  = Oj::get_problem_num(0);

        return $ret;
    }

}
