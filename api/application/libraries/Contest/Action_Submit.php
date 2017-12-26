<?php defined('BASEPATH') OR exit('No direct script access allowed');
//比赛中的题目提交
class Action_Submit {
    CONST CODE_MAX_LENGTH = 100000;
    CONST DATA_LIST       = ['source_code', 'order_id', 'language', 'contest_id'];
    CONST LANGUAGE_LIST   = [1, 2]; //c c++

    private function filter($user_id) {
        $post_data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($post_data)) {
            throw new Exception('数据传输格式有误', 100);
        }

        foreach (self::DATA_LIST as $value) {
            if (empty($post_data[$value])) {
                throw new Exception($value.'不能为空', 400);
            }
        }

        $contest = Oj::get_contest($post_data['contest_id']);
        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        } else if (strtotime($contest['start_time']) > time()) {
            throw new Exception('比赛未开始', 400);
        } else if (strtotime($contest['end_time']) < time()) {
            throw new Exception('比赛已结束', 400);
        }

        $contest_user = Oj::get_contest_user($user_id, $post_data['contest_id']);
        if (empty($contest_user)) {
            throw new Exception('你未注册比赛，不能提交题目', 403);
        }

        $problem_id = Oj::get_problem_id_by_contest($post_data['contest_id'], $post_data['order_id']);
        $problem = Oj::get_problem($problem_id, 0);

        if (strlen($post_data['source_code']) > self::CODE_MAX_LENGTH) {
            throw new Exception('代码不得超过'.self::CODE_MAX_LENGTH.'字节', 100);
        } else if (empty($problem_id) && empty($problem)) {
            throw new Exception('题目不存在', 100);
        } else if (!in_array($post_data['language'], self::LANGUAGE_LIST)) {
            throw new Exception('语言不存在', 100);
        }

        $post_data['problem_id'] = $problem_id;
        $post_data['time_limit'] = $problem['time_limit'];
        $post_data['memory_limit'] = $problem['memory_limit'];

        return $post_data;
    }

    public function execute() {
        $login_data = parse_login();
        $ret['login'] = !empty($login_data);

        if ($ret['login']) {

            $data = $this->filter($login_data['user_id']);

            $solution_data = [
                'problem_id'   => $data['problem_id'],
                'source_code'  => $data['source_code'],
                'language'     => $data['language'],
                'code_length'  => strlen($data['source_code']),
                'user_id'      => $login_data['user_id'],
                'contest_id'   => $data['contest_id']
            ];

            $solution_id = Oj::insert_solution($solution_data);
            if (!$solution_id) {
                throw new Exception('数据传送失败', 500);
            }

            $data = [
                'solution_id' => intval($solution_id),
                'problem_id'  => intval($data['problem_id']),
                'language'    => intval($data['language']),
                'source_code' => $data['source_code'],
                'time_limit'  => intval($data['time_limit']),
                'memory_limit'=> intval($data['memory_limit'])
            ];
            get_redis()->lpush('solution', json_encode($data));

            $ret['solution'] = true;
        }

        return $ret;
    }

}
