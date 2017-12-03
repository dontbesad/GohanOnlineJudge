<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Add {
    const UPLOAD_DIR = '/home/yy/web/OJ/Judge/data/';
    const DATA_LIST = [
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
        $data = [];
        foreach (self::DATA_LIST as $key => $value) {
            if ($value['must'] && empty($_POST[$key])) {
                throw new Exception($key.'不能为空', 100);
            } else if (!$value['must'] && empty($_POST[$key])) {
                continue;
            }
            $data[$key] = $_POST[$key];
        }
        if (empty($_FILES['input_file']['tmp_name'])) {
            throw new Exception("输入文件上传失败", 100);
        } else if (empty($_FILES['output_file']['tmp_name'])) {
            throw new Exception("输出文件上传失败", 100);
        }
        return $data;
    }
    //第几页，每页多少记录
    public function execute() {
        $data = $this->filter();

        if ($problem_id = Oj::insert_problem($data)) {

            $dir = self::UPLOAD_DIR.$problem_id;
            $this->save_file($dir);
        } else {
            throw new Exception('后台数据保存错误', 500);
        }

        return true;
    }

    private function save_file($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775);
        }

        if (!move_uploaded_file($_FILES['input_file']['tmp_name'], $dir.'/data.in')) {
            throw new Exception('输入文件出错', 500);
        }
        if (!move_uploaded_file($_FILES['output_file']['tmp_name'], $dir.'/data.out')) {
            throw new Exception('输出文件出错', 500);
        }
    }

}
