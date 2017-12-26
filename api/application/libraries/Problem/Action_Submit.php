<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Submit {
    CONST CODE_MAX_LENGTH = 100000;
    CONST DATA_LIST       = ['source_code', 'problem_id', 'language'];
    CONST LANGUAGE_LIST   = [1, 2]; //c c++

    private function filter($user_id) {

        $post_json = file_get_contents("php://input");
        $data = json_decode($post_json, true);

        if (empty($data) || !is_array($data)) {
            throw new Exception('数据传输错误', 100);
        } else {
            foreach (self::DATA_LIST as $key => $name) {
                if (empty($data[$name])) {
                    throw new Exception($name.'不能为空', 404);
                }
            }
        }

        $visible = check_admin($user_id) ? 0 : 1; //管理员可以提交不可见的题目
        $problem = Oj::get_problem($data['problem_id'], $visible);

        if (empty($problem)) {
            throw new Exception('题目不存在', 100);
        } else if (strlen($data['source_code']) > self::CODE_MAX_LENGTH) {
            throw new Exception('代码不得超过'.self::CODE_MAX_LENGTH.'字节', 100);
        } else if (!in_array($data['language'], self::LANGUAGE_LIST)) {
            throw new Exception('语言不存在', 100);
        }
        $data['time_limit']   = $problem['time_limit'];
        $data['memory_limit'] = $problem['memory_limit'];

        return $data;
    }

    //需要登录，如果是不可见题目只有管理员才能提交
    public function execute() {
        $login_data = parse_login();
        $ret['login'] = !empty($login_data);

        //这里需要判断普通用户不能提交隐藏的题目
        if ($ret['login']) {
            $data = $this->filter($login_data['user_id']);

            $solution_data = [
                'problem_id'   => $data['problem_id'],
                'source_code'  => $data['source_code'],
                'language'     => $data['language'],
                'code_length'  => strlen($data['source_code']),
                'user_id'      => $login_data['user_id']
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
