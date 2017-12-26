<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Upload_Image {

    private function filter() {

        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        if (empty($_FILES)) {

            throw new Exception('没有上传的文件', 400);

        } else {

            if (!file_exists(OJ_UPLOAD_IMAGE_DIR)) {
                mkdir(OJ_UPLOAD_IMAGE_DIR, 0777);
            }

            $file_arr = [];

            foreach ($_FILES as $file) {

                $type_arr = explode('/', $file['type']);
                if (stripos($type_arr[0], 'image') === false) {

                    throw new Exception('上传的文件中存在不是图片的文件', 400);

                } else if ($file['size'] > OJ_IMAGE_MAX_SIZE) {

                    throw new Exception('上传的文件中的大小不能超过360K', 400);

                }
                $file_arr[] = [
                    'tmp_name' => $file['tmp_name'],
                    'ext_name' => $type_arr[1]
                ];
            }

            $data = [];
            foreach ($file_arr as $file) {

                $upload_name = date('YmdHis').uniqid().'.'.$file['ext_name'];
                $upload_file = OJ_UPLOAD_IMAGE_DIR.$upload_name;
                if (!move_uploaded_file($file['tmp_name'], $upload_file)) {
                    throw new Exception('上传文件失败，可能是权限问题', 500);
                }

                $data[] = OJ_IMAGE_URL.$upload_name;
            }

            return $data;
        }


    }

    public function execute() {

        return $this->filter();
    }

}
