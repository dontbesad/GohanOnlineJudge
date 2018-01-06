<?php defined('BASEPATH') OR exit('No direct script access allowed');
//显示错误信息
class Action_Compilation_Error {

    private function filter($solution_id) {
        if (empty($solution_id) || !preg_match('/^\d+$/', $solution_id)) {
            throw new Exception('参数不正确', 100);
        }

        $solution = Oj::get_solution($solution_id);
        if (empty($solution)) {
            throw new Exception('提交记录不存在', 100);
        } else if ($solution['result'] != 9) {
            throw new Exception('无CE信息', 400);
        }

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录查看', 403);
        }

        if (!check_admin() && $login_data['user_id'] != $solution['user_id']) {
            throw new Exception('你没有权限看此CE信息', 403);
        }
        return $solution['error'];
    }

    public function execute($solution_id) {
        $ret['error'] = $this->filter($solution_id);
        return $ret;
    }

}
