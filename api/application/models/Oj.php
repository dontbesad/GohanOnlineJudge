<?php
class Oj extends CI_Model {
    /**
     * 获取题目列表指定页的题目数量
     */
    static public function get_problem_list_page($offset, $limit, $visible=1) {
        if (!isset($offset) || !isset($limit)) {
            return [];
        }
        $problem_list = get_db()
            ->select('problem_id, title, source, accepted_num, submit_num')
            ->limit($limit, $offset)
            ->get_where('sys_problem', ['visible' => $visible])
            ->result_array();
        return $problem_list;
    }
    /**
     * 获取题目的详细信息
     */
    static public function get_problem($problem_id, $visible=1) {
        if (!isset($problem_id)) {
            return [];
        }
        $problem = get_db()
            ->select('*')
            ->get_where('sys_problem', ['visible' => $visible, 'problem_id' => $problem_id])
            ->row_array();
        return $problem;
    }


    //得到所有可见题目数量
    static public function get_problem_num($visible = 1) {
        $problem = get_db()
            ->select('COUNT(1) AS num')
            ->get_where('sys_problem', ['visible' => $visible])
            ->row_array();
        return empty($problem) ? 0 : $problem['num'];
    }

    static public function get_solution_by_contest($select_data, $where_data) {
        if (empty($select_data) || empty($where_data)) {
            return [];
        }
        $where_data['valid'] = 1;
        $solution_list_contest = get_db()
            ->select($select_data)
            ->order_by('solution_id', 'DESC')
            ->get_where('sys_solution', $where_data)
            ->result_array();
        return $solution_list_contest;
    }

    //获取提交记录列表(不算比赛的)
    static public function get_solution_list_page($offset, $limit, $contest_id=0, $valid=1) {
        if (!isset($offset) || !isset($limit)) {
            return [];
        }
        $solution_list = get_db()
            ->select('solution_id,problem_id,user_id,contest_id,runtime,memory,result,submit_time,code_length,language')
            ->limit($limit, $offset)
            ->order_by('solution_id', 'DESC')
            ->get_where('sys_solution', ['valid' => $valid, 'contest_id' => $contest_id])
            ->result_array();
        return $solution_list;
    }
    //获取提交总数(不算比赛的)
    static public function get_solution_num($contest_id=0, $valid=1) {
        $solution = get_db()
            ->select('COUNT(1) AS num')
            ->get_where('sys_solution', ['valid' => $valid, 'contest_id' => $contest_id])
            ->row_array();
        return empty($solution) ? 0 : $solution['num'];
    }

    static public function get_contest($contest_id) {
        if (!isset($contest_id)) {
            return [];
        }
        $contest = get_db()
            ->select('*')
            ->get_where('sys_contest', ['contest_id' => $contest_id])
            ->row_array();
        return $contest;
    }

    //获得比赛列表
    static public function get_contest_list_page($offset, $limit) {
        if (!isset($offset) || !isset($limit)) {
            return [];
        }
        $problem_list = get_db()
            ->select('contest_id, title, start_time, end_time, private')
            ->limit($limit, $offset)
            ->get_where('sys_contest')
            ->result_array();
        return $problem_list;
    }
    //得到所有比赛题目数目
    static public function get_contest_num() {
        $contest = get_db()
        ->select('COUNT(1) AS num')
        ->get_where('sys_contest')
        ->row_array();
        return empty($contest) ? 0 : $contest['num'];
    }

    //获取比赛中的题目
    static public function get_problem_list_by_contest($contest_id) {
        $problem_list_contest = get_db()
            ->select('title,problem_id,order_id')
            ->order_by('order_id', 'ASC')
            ->get_where('sys_contest_problem', ['contest_id' => $contest_id])
            ->result_array();
        return $problem_list_contest;
    }

    //获取比赛中的某个题目id
    static public function get_problem_id_by_contest($contest_id, $order_id) {
        $problem = get_db()
            ->select('problem_id')
            ->get_where('sys_contest_problem', ['contest_id' => $contest_id, 'order_id' => $order_id])
            ->row_array();
        return empty($problem['problem_id']) ? 0 : $problem['problem_id'];
    }

    //获取比赛中的某个题目order_id
    static public function get_order_id_by_contest($contest_id, $problem_id) {
        $order = get_db()
            ->select('order_id')
            ->get_where('sys_contest_problem', ['contest_id' => $contest_id, 'problem_id' => $problem_id])
            ->row_array();
        return empty($order['order_id']) ? 0 : $order['order_id'];
    }

    //获取比赛中的某一题
    static public function get_problem_by_contest($where_data) {
        if (empty($where_data) || !is_array($where_data)) {
            return [];
        }
        $problem_contest = get_db()
            ->select('*')
            ->get_where('sys_problem', $where_data)
            ->row_array();
        return $problem_contest;
    }

    //获取用户信息
    static public function get_user_info($username, $password=NULL) {
        if (empty($password)) {
            $user = get_db()
                ->select('user_id, username, email')
                ->get_where('sys_user', ['username' => $username])
                ->row_array();
        } else {
            $user = get_db()
                ->select('user_id, username, email')
                ->get_where('sys_user', ['username' => $username, 'password' => $password])
                ->row_array();
        }
        return $user;
    }

    //查看所有的后台权限
    static public function get_rule_list() {
        $rule_list = get_db()
            ->select('rule_id,name')
            ->get_where('cfg_rule')
            ->result_array();
        return $rule_list;
    }

    //查看用户在某个api下有没有权限限制
    static public function check_permission($user_id, $class=strtolower(get_instance()->router->fetch_class())
    ,$method=strtolower(get_instance()->router->fetch_method()) {
        $rule = get_db()
            ->select('rule_id,name')
            ->get_where('cfg_rule', ['class' => $class, 'method' => $method])
            ->row_array();
        if (empty($rule)) {
            return true;
        }
        $user_rule = get_db()
            ->select('')
            ->get_where('sys_user_rule', [`user_id` => $user_id, `rule_id` => $rule['rule_id']])
            ->row_array();
        return !empty($user_rule);
    }
    //根据id获取user信息
    static public function get_user_info_by_id($user_id) {
        $user = get_db()
            ->select('username')
            ->get_where('sys_user', ['user_id' => $user_id])
            ->row_array();
        return $user;
    }
    //注册用户
    static public function insert_user_info($data) {
        get_db()
            ->insert('sys_user', $data);
        return get_db()->insert_id();
    }
    //提交记录
    static public function insert_solution($data) {
        get_db()
            ->insert('sys_solution', $data);
        return get_db()->insert_id();
    }
    //添加题目
    static public function insert_problem($data) {
        get_db()
            ->insert('sys_problem', $data);
        return get_db()->insert_id();
    }
    //添加比赛
    static public function insert_contest($data) {
        get_db()
            ->insert('sys_contest', $data);
        return get_db()->insert_id();
    }
}
