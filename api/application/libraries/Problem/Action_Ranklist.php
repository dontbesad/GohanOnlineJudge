<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Ranklist {

    private function filter($page, $size) {
        if (!isset($page) || !isset($size)
            || $page < 1 || $size < 1
            || !preg_match('/^\d+$/', $page) || !preg_match('/^\d+$/', $size)) {
            throw new Exception('页面不存在', 404);
        }
    }
    //第几页，每页多少记录
    public function execute($page, $size) {
        $this->filter($page, $size);

        $ret['list'] = Oj::get_ranklist_page(($page - 1)*$size, $size);
        $ret['num']  = Oj::get_user_num();

        return $ret;
    }

}
