<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Submit {
    CONST CODE_MAX_LENGTH = 100000;
    CONST DATA_LIST       = ['source_code', 'problem_id', 'language'];
    CONST LANGUAGE_LIST   = [1, 2]; //c c++

    private function filter() {
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
        
        if (strlen($data['source_code']) > self::CODE_MAX_LENGTH) {
            throw new Exception('代码不得超过'.self::CODE_MAX_LENGTH.'字节', 100);
        } else if (empty(Oj::get_problem($data['problem_id']))) {
            throw new Exception('题目不存在', 100);
        } else if (!in_array($data['language'], self::LANGUAGE_LIST)) {
            throw new Exception('语言不存在', 100);
        }
        return $data;
    }

    public function execute() {
        $login_data = parse_login();
        $ret['login'] = !empty($login_data);

        $data = $this->filter();

        if ($ret['login']) {

            $solution_data = [
                'problem_id'   => $data['problem_id'],
                'source_code'  => $data['source_code'],
                'language'     => $data['language'],
                'code_length'  => strlen($data['source_code']),
                'user_id'      => $login_data['user_id']
            ];

            if (!Oj::insert_solution($solution_data)) {
                throw new Exception('数据传送失败', 500);
            }
            $ret['solution'] = true;

        }

        return $ret;
    }

}
