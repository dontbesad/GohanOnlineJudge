<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Update {
    const DATA_LIST = [
        'problem_id' => [
            'must' => 1,
        ],
        'title' => [
            'must' => 1,
        ],
        'description' => [
            'must' => 1,
        ],
        'input' => [
            'must' => 1,
        ],
        'output' => [
            'must' => 1,
        ],
        'sample_input' => [
            'must' => 1,
        ],
        'sample_output' => [
            'must' => 1,
        ],
        'time_limit' => [
            'must' => 1,
        ],
        'memory_limit' => [
            'must' => 1,
        ],
        'hint' => [
            'must' => 0,
        ],
        'source' => [
            'must' => 0
        ],
        'visible' => [
            'must' => 0,
        ]
    ];

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        $data = [];
        foreach (self::DATA_LIST as $key => $value) {
            if ($value['must'] && empty($_POST[$key])) {
                throw new Exception($key.'不能为空', 100);
            } else if (!$value['must'] && empty($_POST[$key])) {
                continue;
            }
            $data[$key] = $_POST[$key];
        }
        if (empty(Oj::get_problem($_POST['problem_id']))) {
            throw new Exception('题目不存在', 404);
        }

        return $data;
    }
    //第几页，每页多少记录
    public function execute() {

        $data = $this->filter();
        $problem_id = $data['problem_id'];
        unset($data['problem_id']);

        Oj::update_problem($problem_id, $data);

        $dir = OJ_UPLOAD_DATA_DIR.$problem_id;
        $this->update_file($dir);

        return true;
    }

    private function update_file($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775);
        }
        if (!empty($_FILES['input_file']['tmp_name'])) {
            if (!move_uploaded_file($_FILES['input_file']['tmp_name'], $dir.'/data.in')) {
                throw new Exception('输入文件出错', 500);
            }
        }
        if (!empty($_FILES['output_file']['tmp_name'])) {
            if (!move_uploaded_file($_FILES['output_file']['tmp_name'], $dir.'/data.out')) {
                throw new Exception('输出文件出错', 500);
            }
        }
    }

}
