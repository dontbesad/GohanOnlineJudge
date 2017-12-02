<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends MY_Controller {
	protected static $api_list = [
		'list', 'test',
	];

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

}
