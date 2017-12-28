<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
	protected static $api_list = [
		'login', 'register'
	];

	public function login() {
		$this->load->library('User/Action_Login');
		try {
            $post_data = $this->post_data();
			$ret['data'] = $this->action_login->execute($post_data);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

    public function register() {
        $this->load->library('User/Action_Register');
		try {
            $post_data = $this->post_data();
			$ret['data'] = $this->action_register->execute($post_data);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
    }

    public function verify() {
        $this->load->library('User/Action_Verify');
		try {
			$ret['data'] = $this->action_verify->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
    }

	//退出登录
    public function quit() {
        $this->load->library('User/Action_Quit');
		try {
			$ret['data'] = $this->action_quit->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
    }

	//用户信息
	public function info($user_id=0) {
		$this->load->library('User/Action_Info');
		try {
			$ret['data'] = $this->action_info->execute($user_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

}
