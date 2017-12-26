<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Quit {

    private function filter() {

    }

    public function execute() {
        setcookie('token', 0, time() - 1, '/');
        return true;
    }

}
