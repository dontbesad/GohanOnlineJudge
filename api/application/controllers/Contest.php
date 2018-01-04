<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends MY_Controller {
	protected static $api_list = [
		'list', 'test',
	];
	//比赛列表
	public function list($page=1, $size=10) {
		$this->load->library('Contest/Action_List');
		try {
			$ret['data'] = $this->action_list->execute($page, $size);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}
	//比赛基本信息
	public function info($contest_id=0) {
		$this->load->library('Contest/Action_Info');
		try {
			$ret['data'] = $this->action_info->execute($contest_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function problem_list($contest_id=0) {
		$this->load->library('Contest/Action_Problem_List');
		try {
			$ret['data'] = $this->action_problem_list->execute($contest_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function status($contest_id=0, $page=1, $size=10) {
		$this->load->library('Contest/Action_Status');
		try {
			$ret['data'] = $this->action_status->execute($contest_id, $page, $size);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function rank($contest_id=0) {
		$this->load->library('Contest/Action_Rank');
		try {
			$ret['data'] = $this->action_rank->execute($contest_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function problem($contest_id, $order_id) {
		$this->load->library('Contest/Action_Problem');
		try {
			$ret['data'] = $this->action_problem->execute($contest_id, $order_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function submit() {
		$this->load->library('Contest/Action_Submit');
		try {
			$ret['data'] = $this->action_submit->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}
	//post
	public function register() {
		$this->load->library('Contest/Action_Register');
		try {
			$ret['data'] = $this->action_register->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function recent_contest() {
		$this->load->library('Contest/Action_Recent_Contest');
		try {
			$ret['data'] = $this->action_recent_contest->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

}
