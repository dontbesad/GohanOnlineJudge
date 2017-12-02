<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Problem extends MY_Controller {
	protected static $api_list = [
		'list', 'test',
	];

	public function add() {
		$this->load->library('Admin/Problem/Action_Add');
		try {
			$ret['data'] = $this->action_add->execute();
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();
		}
		$this->json_response($ret);
	}

}
