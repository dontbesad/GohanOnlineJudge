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

    //获取提交记录列表
    static public function get_solution_list_page($offset, $limit, $valid=1) {
        if (!isset($offset) || !isset($limit)) {
            return [];
        }
        $solution_list = get_db()
            ->select('solution_id,problem_id,user_id,contest_id,runtime,memory,result,submit_time,code_length,language')
            ->limit($limit, $offset)
            ->get_where('sys_solution', ['valid' => $valid])
            ->result_array();
        return $solution_list;
    }
    //获取提交总数
    static public function get_solution_num($valid = 1) {
        $solution = get_db()
            ->select('COUNT(1) AS num')
            ->get_where('sys_solution', ['valid' => $valid])
            ->row_array();
        return empty($solution) ? 0 : $solution['num'];
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
    static public function get_problem_list_contest($contest_id) {
        $problem_list_contest = get_db()
            ->select('*')
            ->get_where('sys_contest_problem', ['contest_id' => $contest_id])
            ->result_array();
        return $problem_list_contest;
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
}
