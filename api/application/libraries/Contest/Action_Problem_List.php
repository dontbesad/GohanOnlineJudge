<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Problem_List {

    private function filter($contest_id) {

        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('数据传输有误', 100);
        }
        $contest = Oj::get_contest($contest_id);
        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        }
        return $contest;
    }
    //需要判断private(need login&in_contest_list), public(not need)
    public function execute($contest_id) {
        $login_info   = parse_login();
        $ret['login'] = !empty($login_info);
        $ret['display'] = false;

        $contest = $this->filter($contest_id);
        if ($ret['login']) {
            if ($contest['private']) {
                //
            } else {

                $ret['display'] = true;
                if (time() >= strtotime($contest['start_time'])
                    && time() <= strtotime($contest['end_time'])) {

                    $ret['list'] = $this->get_contest_problem_list($contest_id);
                }
            }
        }
        //var_dump($ret);

        return $ret;
    }

    private function get_contest_problem_list($contest_id) {
        $contest_problem_list = Oj::get_problem_list_by_contest($contest_id);
        if (empty($contest_problem_list)) {
            return [];
        }
        foreach ($contest_problem_list as &$problem) {
            $where_data = [
                'contest_id' => $contest_id,
                'problem_id' => $problem['problem_id']
            ];
            $submit = Oj::get_solution_by_contest('COUNT(DISTINCT(`user_id`)) AS num', $where_data);
            $where_data['result'] = 1;
            $solved = Oj::get_solution_by_contest('COUNT(DISTINCT(`user_id`)) AS num', $where_data);

            $problem['submit_num'] = isset($submit[0]['num']) ? $submit[0]['num'] : 0;
            $problem['solved_num'] = isset($submit[0]['num']) ? $submit[0]['num'] : 0;
        }

        return $contest_problem_list;
    }

}
