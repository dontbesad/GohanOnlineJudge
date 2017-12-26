<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Problem {

    private function filter($contest_id, $order_id) {

        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)
        || empty($order_id) || !preg_match('/^\w+$/', $order_id)) {
            throw new Exception('数据传输有误', 100);
        }

        $problem_id = Oj::get_problem_id_by_contest($contest_id, $order_id);
        if (empty($problem_id)) {
            throw new Exception('题目编号不存在', 404);
        }
        $problem = Oj::get_problem_by_contest(['problem_id' => $problem_id]);
        $contest = Oj::get_contest($contest_id);
        if (empty($problem)) {
            throw new Exception('题目不存在', 404);
        } else if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        } else if (time() < strtotime($contest['start_time'])) {
            throw new Exception('比赛未开始，不能查看题目', 403);
        }

        return [$problem, $contest];
    }
    //需要判断private(need login&in_contest_list), public(not need)
    public function execute($contest_id, $order_id) {
        $login_info     = parse_login();
        $ret['login']   = !empty($login_info);

        list($problem, $contest) = $this->filter($contest_id, $order_id);

        if ($contest['private']) {
            if ($ret['login']) {
                $contest_user = Oj::get_contest_user($login_info['user_id'], $contest_id);
                if (empty($contest_user)) {
                    throw new Exception('你未注册私有比赛，不能查看题目', 403);
                }
            } else {
                throw new Exception('请先登录查看私有比赛的题目', 100);
            }
        }

        $ret['title']        = $problem['title'];
        $ret['time_limit']   = $problem['time_limit'];
        $ret['memory_limit'] = $problem['memory_limit'];
        $ret['description']  = $problem['description'];
        $ret['input']        = $problem['input'];
        $ret['output']       = $problem['output'];
        $ret['sample_input'] = $problem['sample_input'];
        $ret['sample_output']= $problem['sample_output'];
        $ret['hint']         = isset($problem['hint']) ? $problem['hint'] : '';
        $ret['source']       = isset($problem['source']) ? $problem['source'] : '';
        $ret['order_id']     = $order_id;

        return $ret;
    }

}
