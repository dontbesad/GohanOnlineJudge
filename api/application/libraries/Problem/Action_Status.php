<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Status {

    const RESULT_LIST = [
        0 => 'Queuing',
        1 => 'Accepted',
        2 => 'Wrong Answer',
        3 => 'Presentation Error',
        4 => 'Output Limit Exceeded',
        5 => 'Time Limit Exceeded',
        6 => 'Memory Limit Exceeded',
        7 => 'Runtime Error',
        8 => 'Malicious Code',
        9 => 'Compilation Error',
        10 => 'System Error',
        11 => 'Compiling',
        12 => 'Running',
    ];

    const LANGUAGE_LIST = [
        1 => 'C',
        2 => 'C++'
    ];

    private function filter($page, $size) {
        if (!isset($page) || !isset($size)
            || $page < 1 || $size < 1
            || !preg_match('/^\d+$/', $page) || !preg_match('/^\d+$/', $size)) {
            throw new Exception('页面不存在', 404);
        }
    }

    public function execute($page, $size) {
        $this->filter($page, $size);
        $ret['list'] = Oj::get_solution_list_page(($page - 1)*$size, $size); //默认不是在比赛提交中的记录
        if (!empty($ret['list'])) {
            foreach ($ret['list'] as &$row) {
                $user = Oj::get_user_info_by_id($row['user_id']);
                $row['username'] = $user['username'];
                if ($row['result'] == 1) {
                    $row['result'] = self::RESULT_LIST[$row['result']];
                } else {
                    $row['result'] = self::RESULT_LIST[$row['result']];
                }

                $row['language'] = self::LANGUAGE_LIST[$row['language']];
            }
        }
        $ret['num']  = Oj::get_solution_num();
        return $ret;
    }

}
