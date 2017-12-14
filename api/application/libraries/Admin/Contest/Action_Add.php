<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Add {
    const DATA_LIST = [
        'title' => [
            'must' => 1,
        ],
        'description' => [
            'must' => 0,
        ],
        'start_time' => [
            'must' => 1,
        ],
        'end_time' => [
            'must' => 1,
        ],
        'private' => [
            'must' => 0,
        ]
    ];

    private function filter() {
        $post_data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($post_data)) {
            throw new Exception('错误的传输格式', 200);
        }

        foreach (self::DATA_LIST as $key => $value) {
            if ($value['must'] && empty($post_data[$key])) {
                throw new Exception($key.'不能为空', 200);
            }
        }

        return $post_data;
    }

    public function execute() {
        $data = $this->filter();
        //这里还需要题目的顺带添加，以及后端自动为添加的题目进行编号(1000, 1001...)
        if (Oj::insert_contest($data)) {
            return true;
        }

        return false;
    }

}
