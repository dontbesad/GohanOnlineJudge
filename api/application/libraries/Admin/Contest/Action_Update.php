<?php defined('BASEPATH') OR exit('No direct script access allowed');
//Admin - contest_update
//比赛修改
class Action_Update {
    const DATA_LIST = [
        'contest_id' => [
            'must' => 1,
        ],
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
        if (empty(Oj::get_contest($post_data['contest_id']))) {
            throw new Exception('对应的比赛不存在', 404);
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

        $orig_problem_list = Oj::get_problem_list_by_contest($post_data['contest_id']); //原先的题目
        if (!empty($orig_problem_list)) {
            //原先的题目比现在的题目多
            if (empty($problem_list)) {
                throw new Exception('比赛题目为空(比赛题目跟原先相比不能减少,只能在原先的顺序基础上增加)', 400);
            } else {
                $problem_id_arr = array_column($problem_list, 'problem_id');
                foreach ($orig_problem_list as $problem) {
                    $index = ord($problem['order_id']) - 65;
                    if (empty($problem_list[$index])) { //post与数据库 不能一一对应
                        throw new Exception($problem['problem_id'].'号题目缺少(比赛题目跟原先相比不能减少,只能在原先的顺序基础上增加)', 400);
                    }
                    unset($problem_list[$index]);
                }
            }
        }

        $post_data['problem_list'] = $problem_list; //新增的题目

        return $post_data;
    }

    public function execute() {

        $data = $this->filter();

        $contest_id   = $data['contest_id'];
        unset($data['contest_id']);
        $problem_list = $data['problem_list'];
        unset($data['problem_list']);

        Oj::update_contest($contest_id, $data);

        $orig_problem_list = Oj::get_problem_list_by_contest($contest_id);

        if (!empty($problem_list)) {
            $index = count($orig_problem_list);;
            foreach ($problem_list as &$problem) {
                $problem['order_id']   = self::ORDER_LIST[$index++];
                $problem['contest_id'] = $contest_id;
            }
            Oj::insert_contest_problem($problem_list);
        }


        return true;
    }

}
