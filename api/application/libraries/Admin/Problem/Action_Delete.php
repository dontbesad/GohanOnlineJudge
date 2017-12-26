<?php defined('BASEPATH') OR exit('No direct script access allowed');
//删除题目&题目数据
class Action_Delete {

    private function filter($problem_id) {
        $login_data = parse_login();
        if (empty($login_data)) {
            throw new Exception('请先登录', 403);
        } else if (!check_permission($login_data['user_id'])) {
            throw new Exception('您没有权限访问', 403);
        }

        if (empty($problem_id) || !preg_match('/^\d+$/',$problem_id)) {
            throw new Exception('参数不正确', 400);
        } else if (!Oj::get_problem($problem_id, 0)) {
            throw new Exception('题目不存在', 404);
        }

    }

    public function execute($problem_id) {
        $this->filter($problem_id);

        if (!Oj::delete_problem($problem_id)) {
            throw new Exception('数据库删除失败', 500);
        }
        $problem_dir = OJ_UPLOAD_DATA_DIR . $problem_id . '/';

        if (is_dir($problem_dir) && !$this->delete_all($problem_dir)) {
            throw new Exception('题目数据删除失败', 500);
        }

        return true;
    }

    public function delete_all($dir_name) {
        $dir = opendir($dir_name);
        while (($subdir_name = readdir($dir)) != false) {
            if ($subdir_name == '.' || $subdir_name == '..') {
                continue;
            }
            if (is_dir($subdir_name)) {
                delete_all($subdir_name.$subdir_name.'/');
            } else {
                unlink($dir_name.$subdir_name);
            }
        }
        closedir($dir);
        return rmdir($dir_name);
    }

}
