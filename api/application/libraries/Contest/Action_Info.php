<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Info {

    private function filter($contest_id) {
        if (empty($contest_id) || !preg_match('/^\d+$/', $contest_id)) {
            throw new Exception('数据传输有误', 100);
        }
        $contest = Oj::get_contest($contest_id);
        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        }
        return $contest;
    }

    public function execute($contest_id) {
        $login_info   = parse_login();
        $ret['login'] = !empty($login_info);

        $contest = $this->filter($contest_id);
        $ret['start_time']  = $contest['start_time'];
        $ret['end_time']    = $contest['end_time'];
        $ret['private']     = $contest['private'];
        $ret['description'] = $contest['description'];
        $ret['title']       = $contest['title'];

        if ($ret['private']) {
            //...
        } else {

        }
        return $ret;
    }

}
