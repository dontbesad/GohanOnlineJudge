<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Rank {

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
        
    }

}
