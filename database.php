<?php
class gohan_database {

    private static $_instance;


    public function __construct() {
        file_get_contents
    }
    static public function get_instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    static public function say() {

        echo 'asdasd';
    }

    static public function hello() {
        self::say();
    }
}
