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

	public function list($page=1, $size=10) {
		$this->load->library('Admin/Problem/Action_List');
		try {
			$ret['data'] = $this->action_list->execute($page, $size);
			$ret['code'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['code'] = $e->getCode();

		}
		$this->json_response($ret);
	}
	//富文本编辑器中上传图片文件
	public function upload_image() {
		$this->load->library('Admin/Problem/Action_Upload_Image');
		try {
			$ret['data'] = $this->action_upload_image->execute();
			$ret['errno'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['errno'] = $e->getCode();

		}
		$this->json_response($ret);
	}

	public function delete($problem_id=0) {
		$this->load->library('Admin/Problem/Action_Delete');
		try {
			$ret['data'] = $this->action_delete->execute($problem_id);
			$ret['errno'] = 0;
		} catch (Exception $e) {
			$ret['msg']  = $e->getMessage();
			$ret['errno'] = $e->getCode();

		}
		$this->json_response($ret);
	}

	public function update() {
		echo '404';
	}
}
