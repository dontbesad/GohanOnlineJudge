<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Recent_Contest {

    private function filter() {
    }

    public function execute() {

        $this->filter();
        $recent_contest_json = file_get_contents('http://contests.acmicpc.info/contests.json');
        $ret['list'] = json_decode($recent_contest_json, true);
        return $ret;
    }

}
