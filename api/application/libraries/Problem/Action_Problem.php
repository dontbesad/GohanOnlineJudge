<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Problem {

    private function filter($problem_id) {
        if (!isset($problem_id)
            || !preg_match('/^\d+$/', $problem_id)) {
            throw new Exception('题目不存在', 404);
        }
    }
    //第几页，每页多少记录
    public function execute($problem_id) {
        $this->filter($problem_id);

        $visible = check_admin() ? 0 : 1; //管理员可以看见不可见的题目
        $ret = Oj::get_problem($problem_id, $visible);

        if (empty($ret)) {
            throw new Exception('题目不存在', 403);
        }

        $ret['hint']   = isset($ret['hint']) ? $ret['hint'] : '';
        $ret['source'] = isset($ret['source']) ? $ret['source'] : '';

        return $ret;
    }

}
