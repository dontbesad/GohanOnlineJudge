<?php
class Judge extends MY_Controller {
    private $DATA_LIST = [
        'result' => [
            'must' => 1,
            'preg' => '/^\d+$/',
        ],
        'solution_id' => [
            'must' => 1,
            'preg' => '/^\d+$/',
        ],
        'runtime' => [
            'must' => 1,
            'preg' => '/^\d+$/',
        ],
        'memory' => [
            'must' => 1,
            'preg' => '/^\d+$/',
        ],
        'error' => [
            'must' => 0,
        ]
    ];

    public function update_result() {
        $post_data = file_get_contents('php://input');
        $post_data = json_decode($post_data, true);

        if (empty($post_data)) {
            $this->json_response(['code'=>400, 'msg'=>'数据为空']);
        } else if (!$this->check_update_power()) {
            $this->json_response(['code'=>403, 'msg'=>'Permission denied!']);
        } else {

            foreach ($this->DATA_LIST as $index => $value) {
                if ($value['must'] && (!isset($post_data[$index]) || !preg_match($value['preg'], $post_data[$index]))) {
                    $this->json_response(['code'=>400, 'msg'=>'数据格式不正确']);
                    return false;
                }
            }

            $solution = Oj::get_solution($post_data['solution_id']);
            if (empty($solution)) {
                $this->json_response(['code'=>404, 'msg'=>'提交记录不存在']);
                return ;
            }
            //除了队列ing,编译ing,运行ing
            if ($post_data['result'] >= 1 && $post_data['result'] <= 10) {
                if ($solution['contest_id'] > 0) {
                    $this->update_contest($solution['problem_id'], $solution['contest_id'], $post_data['result'], $solution['user_id'], $solution['submit_time']);
                } else {
                    $this->update_problem($solution['problem_id'], $post_data['result']);
                }
            }

            $this->update_solution($post_data);

            $this->json_response(['code'=>0,'ret'=>true]);
        }
    }

    private function check_update_power() {
        //用于解密post过来的密码是否正确
        return true;
    }

    private function update_solution($data) {
        $update_data = [
            'result'  => $data['result'],
            'runtime' => $data['runtime'],
            'memory'  => $data['memory']
        ];
        if ($data['result'] == 9) {
            $update_data['error'] = $data['error'];
        }
        return Oj::update_solution($data['solution_id'], $update_data);
    }

    private function update_contest($problem_id, $contest_id, $result, $user_id, $submit_time) {

        Oj::update_contest_problem_num($problem_id, $contest_id, 'submit_num');

        $where_data = [
            'user_id'    => $user_id,
            'contest_id' => $contest_id,
            'problem_id' => $problem_id,
            'result'     => 1,
        ];
        $ac_contest_solution = Oj::get_solution_by_contest('COUNT(1) AS num', $where_data);

        if ($result == 1) {
            Oj::update_contest_problem_num($problem_id, $contest_id, 'accepted_num');

            if (empty($ac_contest_solution[0]['num'])) { //要是用户之前没有ac此题
                Oj::update_contest_problem_num($problem_id, $contest_id, 'solved_num');

                $this->update_contest_rank($problem_id, $contest_id, $result, $user_id, $submit_time);
            }
        } else {
            //wa
            var_dump($ac_contest_solution);
            if (empty($ac_contest_solution[0]['num'])) {
                var_dump('houhou');
                $this->update_contest_rank($problem_id, $contest_id, $result, $user_id, $submit_time);
            }

        }

        return true;
    }

    private function update_contest_rank($problem_id, $contest_id, $result, $user_id, $submit_time) {
        $contest      = Oj::get_contest($contest_id);
        $contest_user = Oj::get_contest_user($user_id, $contest_id);
        $order_id     = Oj::get_order_id_by_contest($contest_id, $problem_id);

        $time_stamp   = strtotime($submit_time) - strtotime($contest['start_time']);
        $time         = $time_stamp;
        $state_arr    = empty($contest_user['state']) ? [] : json_decode($contest_user['state'], true);

        $penalty      = $contest_user['penalty'];
        $solved_num   = $contest_user['solved_num'];

        $ac = true;
        $wa_add = 0;

        if ($result == 1) {
            $penalty = $time;
            $solved_num++;

        } else {

            $ac = false;
            $wa_add++;
        }

        if (empty($state_arr[$order_id])) {
            $state_arr[$order_id] = [
                'ac'      => $ac,
                'wa_num'  => $wa_add,
                'ac_time' => $time,
                'order_id'=> $order_id
            ];
        } else {
            $state_arr[$order_id]['ac'] = $ac;
            $state_arr[$order_id]['wa_num'] += $wa_add;
            $state_arr[$order_id]['ac_time'] = $ac ? $time : 0;
        }
        if ($ac) {
            $penalty += $state_arr[$order_id]['wa_num'] * 1200;

            $where_data = [
                'contest_id' => $contest_id,
                'problem_id' => $problem_id,
                'result'     => 1,
            ];
            $ac_contest_solution = Oj::get_solution_by_contest('COUNT(1) AS num', $where_data);
            if (empty($ac_contest_solution[0]['num'])) {
                $state_arr[$order_id]['fb'] = true;
            }
        }

        ksort($state_arr);
        $state = json_encode($state_arr);

        Oj::update_contest_user($contest_id, $user_id, ['state' => $state, 'penalty' => $penalty, 'solved_num' => $solved_num]);

        return true;
    }

    //更新题目中的submit_num和accepted_num
    private function update_problem($problem_id, $contest_id, $result) {
        if ($result < 1 || $result > 10 || $contest_id > 0) {
            return false;
        }
        if ($contest_id > 0) {
            Oj::update_contest_problem_num($problem_id, $contest_id, 'submit_num');
            if ($result == 1) {
                Oj::update_contest_problem_num($problem_id, $contest_id, 'accepted_num');
            }
        } else {
            Oj::update_problem_num($problem_id, 'submit_num');
            if ($result == 1) {
                Oj::update_problem_num($problem_id, 'accepted_num');
            }
        }
    }
}
