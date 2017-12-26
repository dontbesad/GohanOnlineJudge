<?php
class Judge extends MY_Controller {
    public function update_result() {
        $post_data = file_get_contents('php://input');
        $data = json_decode($post_data, true);
        var_dump($post_data);
        var_dump($data);

        if (empty($data)) {
            $this->json_response(['code'=>400, 'msg'=>'数据格式不正确']);
        } else if (!$this->check_update_power()) {
            $this->json_response(['code'=>403, 'msg'=>'Permission denied!']);
        } else {
            $solution = Oj::get_solution($data['solution_id']);
            if (empty($solution)) {
                $this->json_response(['code'=>404, 'msg'=>'提交记录不存在']);
                return ;
            }

            $this->update_problem($solution['problem_id'], $solution['contest_id'], $data['result']);

            $this->update_contest($solution['contest_id'], $data['result'], $solution['user_id']);

            $this->update_solution($data);

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

    private function update_contest($contest_id, $result, $user_id) {
        if ($contest_id <= 0 || empty($user_id) || $result < 1 || $result > 10) {
            return false;
        }
        return true;
    }

    //更新题目中的submit_num和accepted_num
    private function update_problem($problem_id, $contest_id, $result) {
        if ($result < 1 || $result > 10) {
            return false;
        }
        if ($contest_id > 0) {
            Oj::update_contest_problem_num($problem_id, $contest_id, 'submit_num');
            if ($result == 1) {
                Oj::update_problem_num($problem_id, $contest_id, 'accepted_num');
            }
        } else {
            Oj::update_problem_num($problem_id, 'submit_num');
            if ($result == 1) {
                Oj::update_problem_num($problem_id, 'accepted_num');
            }
        }
    }
}
