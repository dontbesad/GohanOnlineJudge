<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Problem_List {

    private function filter($contest_id) {

        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('数据传输有误', 100);
        }
        $contest = Oj::get_contest($contest_id);
        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        } else if (time() < strtotime($contest['start_time'])) {
            throw new Exception('比赛未开始，不能查看题目列表', 403);
        }
        return $contest;
    }
    //需要判断private(need login&in_contest_list), public(not need)
    //Public显示题目列表的话，只需要用户登录且比赛开始之后就行了
    public function execute($contest_id) {
        $login_info   = parse_login();
        $ret['login'] = !empty($login_info);

        $contest = $this->filter($contest_id);

        if ($contest['private']) {
            if ($ret['login']) {
                $contest_user = Oj::get_contest_user($login_info['user_id'], $contest_id);
                if (empty($contest_user)) {
                    throw new Exception('你未注册私有比赛，不能查看题目列表', 403);
                }
            } else {
                throw new Exception('请先登录查看私有比赛的题目列表', 100);
            }
        }

        $ret['list'] = $this->get_contest_problem_list($contest_id, $login_info['user_id']);
        //var_dump($ret);

        return $ret;
    }

    private function get_contest_problem_list($contest_id, $user_id) {
        $contest_problem_list = Oj::get_problem_list_by_contest($contest_id);
        if (empty($contest_problem_list)) {
            return [];
        }

        foreach ($contest_problem_list as &$contest_problem) {
            if (empty($user_id)) {
                $contest_problem['status'] = 0; //0未提交，-1错误, 1AC
                continue;
            }
            $solution_list = Oj::get_solution_by_contest(['result'], ['user_id' => $user_id, 'contest_id' => $contest_id, 'problem_id' => $contest_problem['problem_id']]);
            unset($contest_problem['problem_id']);
            if (empty($solution_list)) {
                $contest_problem['status'] = 0;
            } else if (in_array(1, array_column($solution_list, 'result'))) {
                $contest_problem['status'] = 1;
            } else {
                $contest_problem['status'] = -1;
            }
        }

        return $contest_problem_list;
    }

}
