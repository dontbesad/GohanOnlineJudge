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

    private function filter($contest_id, $page, $size) {
        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('数据传输有误', 100);
        }
        $contest = Oj::get_contest($contest_id);
        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        }
        return $contest;
    }

    public function execute($contest_id, $page, $size) {
        
        $login_data = parse_login();
        $ret['login'] = !empty($login_data);
        $ret['display'] = false;

        if ($ret['login']) {
            $ret['display'] = true;
            $this->filter($contest_id, $page, $size);

            $ret['list'] = Oj::get_solution_list_page(($page - 1)*$size, $size, $contest_id);
            if (!empty($ret['list'])) {
                foreach ($ret['list'] as &$row) {
                    $row['order_id'] = Oj::get_order_id_by_contest($contest_id, $row['problem_id']);
                    unset($row['problem_id']);
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
            $ret['num']  = Oj::get_solution_num($contest_id);

        }
        return $ret;

    }

}
