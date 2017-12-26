<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Rank {

    private function filter($contest_id) {
        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('数据传输有误', 100);
        }
        $contest = Oj::get_contest($contest_id);
        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        } else if (time() < strtotime($contest['start_time'])) {
            throw new Exception('比赛未开始，不能查看排名', 403);
        }

        if ($contest['private']) {
            $login_data = parse_login();
            if (empty($login_data)) {
                throw new Exception('请先登录查看私有比赛的排名', 403);
            }

            $contest_user = Oj::get_contest_user($login_data['user_id'], $contest_id);
            if (empty($contest_user)) {
                throw new Exception('你没有注册私有比赛，不能查看排名~', 403);
            }
        }

        return $contest;
    }

    public function execute($contest_id) {

        $contest = $this->filter($contest_id);

        $contest_user_list = Oj::get_contest_user_list($contest_id);

        foreach ($contest_user_list as &$contest_user) {
            $user = Oj::get_user_info_by_id($contest_user['user_id']);
            $contest_user['username'] = $user['username'];
        }
        
        $ret['list'] = $contest_user_list;

        return $ret;
    }

}
