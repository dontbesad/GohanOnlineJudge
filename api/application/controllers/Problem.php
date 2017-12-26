<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Problem extends MY_Controller {
	protected static $api_list = [
		'list', 'test',
	];

	public function list($page=1, $size=10) {
		$this->load->library('Problem/Action_List');
		try {
			$ret['data'] = $this->action_list->execute($page, $size);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function show($problem_id=1000) {
		$this->load->library('Problem/Action_Problem');
		try {
			$ret['data'] = $this->action_problem->execute($problem_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	//用户提交题目数据
	public function submit() {
		$this->load->library('Problem/Action_Submit');
		try {
			$ret['data'] = $this->action_submit->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function status($page=1, $size=10) {
		$this->load->library('Problem/Action_Status');
		try {
			$ret['data'] = $this->action_status->execute($page, $size);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

	public function source_code($solution_id=1) {
		$this->load->library('Problem/Action_Source_Code');
		try {
			$ret['data'] = $this->action_source_code->execute($solution_id);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

}
