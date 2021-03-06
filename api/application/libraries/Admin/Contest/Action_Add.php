<?php defined('BASEPATH') OR exit('No direct script access allowed');
//Admin - contest_add
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
        ],
        'password' => [
            'must' => 0,
        ],
        'problem_list' => [
            'must' => 0
        ]
    ];

    const ORDER_LIST = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        $post_data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($post_data)) {
            throw new Exception('错误的传输格式', 200);
        }

        foreach (self::DATA_LIST as $key => $value) {
            if ($value['must'] && empty($post_data[$key])) {
                throw new Exception($key.'不能为空', 200);
            }
        }

        if (strtotime($post_data['end_time']) <= strtotime($post_data['start_time'])) {
            throw new Exception('比赛的结束时间不能小于开始时间', 400);
        }

        if (!empty($post_data['problem_list']) && !preg_match('/^[,\d]+$/', $post_data['problem_list'])) {
            throw new Exception('添加至比赛的题目格式错误', 400);
        }

        $problem_arr = explode(',', $post_data['problem_list']);
        $problem_list = [];
        foreach ($problem_arr as &$problem_id) {
            $problem = Oj::get_problem($problem_id, 0);
            if (empty($problem_id)) {
                throw new Exception('请输入正确的比赛题目格式,比如:1000,1001', 100);
            } else if (empty($problem)) {
                throw new Exception($problem_id.'题目不存在', 404);
            }

            $problem_list[] = [
                'problem_id' => $problem_id,
                'title'      => $problem['title'],
            ];
        }

        if (count($problem_list) > 16) {
            throw new Exception('比赛题目数量不得超过16个', 100);
        }
        $post_data['problem_list'] = $problem_list;

        return $post_data;
    }

    public function execute() {
        
        $data = $this->filter();

        $problem_list = $data['problem_list'];
        unset($data['problem_list']);

        $contest_id = Oj::insert_contest($data);

        if (!$contest_id) {
            throw new Exception('比赛添加失败', 500);
        }

        if (!empty($problem_list)) {
            $index = 0;
            foreach ($problem_list as &$problem) {
                $problem['order_id']   = self::ORDER_LIST[$index++];
                $problem['contest_id'] = $contest_id;
            }
            Oj::insert_contest_problem($problem_list);
        }

        return true;
    }

}
