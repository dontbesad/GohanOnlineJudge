<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Problem {

    private function filter($problem_id) {
        if (!isset($problem_id)
            || !preg_match('/^\d+$/', $problem_id)) {
            throw new Exception('页面不存在', 404);
        }
    }
    //第几页，每页多少记录
    public function execute($problem_id) {
        $this->filter($problem_id);
        $ret = Oj::get_problem($problem_id);

        if (empty($ret)) {
            throw new Exception('页面不存在', 404);
        }

        $ret['accpeted_percent'] = 0;
        $ret['solved_percent']   = 0;
        if ($ret['submit_num']) {
            $ret['accpeted_percent'] = $ret['accepted_num'] * 1.0 / $ret['submit_num'];
            $ret['solved_percent']   = $ret['solved_num'] * 1.0 / $ret['submit_num'];
        }

        return $ret;
    }

}
