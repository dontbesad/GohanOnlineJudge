<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Status {

    private function filter($page, $size) {
        if (!isset($page) || !isset($size)
            || $page < 1 || $size < 1
            || !preg_match('/^\d+$/', $page) || !preg_match('/^\d+$/', $size)) {
            throw new Exception('页面不存在', 404);
        }
    }

    public function execute($page, $size) {
        $this->filter($page, $size);
        $ret['list'] = Oj::get_solution_list_page(($page - 1)*$size, $size);
        if (!empty($ret['list'])) {
            foreach ($ret['list'] as &$row) {
                $user = Oj::get_user_info_by_id($row['user_id']);
                $row['username'] = $user['username'];
            }
        }
        $ret['num']  = Oj::get_solution_num();
        return $ret;
    }

}
