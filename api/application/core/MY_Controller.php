<?php

class MY_Controller extends CI_Controller {
    protected static $api_list = [];

    public function __construct() {
        parent::__construct();
    }

    public function post_data() {
        $post_json_data = @file_get_contents('php://input'); //json
        return json_decode($post_json_data, true);
    }

    public function json_response($data) {
        header('Content-type: application/json');
        echo json_encode($data);
    }
}
