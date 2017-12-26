<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
	protected static $api_list = [
		'login', 'register'
	];

	public function login() {
		$this->load->library('Admin/User/Action_Login');
		try {
			$ret['data'] = $this->action_login->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

    public function verify() {
        $this->load->library('Admin/User/Action_Verify');
		try {
			$ret['data'] = $this->action_verify->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
    }

	public function search_user() {
		$this->load->library('Admin/User/Action_Search_User');
		try {
			$ret['data'] = $this->action_search_user->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	//管理员列表
    public function admin_list() {
		$this->load->library('Admin/User/Action_Admin_List');
		try {
			$ret['data'] = $this->action_admin_list->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
    }

	public function role_list() {
		$this->load->library('Admin/User/Action_Role_List');
		try {
			$ret['data'] = $this->action_role_list->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	//权限修改
	public function admin_grant() {
		$this->load->library('Admin/User/Action_Admin_Grant');
		try {
			$ret['data'] = $this->action_admin_grant->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

}
