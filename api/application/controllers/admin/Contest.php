<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends MY_Controller {
	protected static $api_list = [
		'list', 'test',
	];

	public function add() {
		$this->load->library('Admin/Contest/Action_Add');
		try {
			$ret['data'] = $this->action_add->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();

		}
		$this->json_response($ret);
	}

	public function list($page=1, $size=10) {
		$this->load->library('Admin/Contest/Action_List');
		try {
			$ret['data'] = $this->action_list->execute($page, $size);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function delete($contest_id) {
		$this->load->library('Admin/Contest/Action_Delete');
		try {
			$ret['data'] = $this->action_delete->execute($contest_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	//update时候展示比赛信息
	public function info($contest_id=0) {
		$this->load->library('Admin/Contest/Action_Info');
		try {
			$ret['data'] = $this->action_info->execute($contest_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function update() {
		$this->load->library('Admin/Contest/Action_Update');
		try {
			$ret['data'] = $this->action_update->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}
}
