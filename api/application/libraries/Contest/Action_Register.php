<?php defined('BASEPATH') OR exit('No direct script access allowed');
//比赛注册
class Action_Register {
    const DATA_LIST = [
        'contest_id', 'contest_password'
    ];

    private function filter() {
        $post_json = file_get_contents('php://input');
        $post_data = json_decode($post_json, true);
        if (empty($post_data['contest_id']) || !preg_match('/^\d+$/', $post_data['contest_id'])) {
            throw new Exception('比赛编号格式不正确', 100);
        }

        $login_data = parse_login();
        $contest    = Oj::get_contest($post_data['contest_id']);

        if (empty($contest)) {
            throw new Exception('比赛不存在', 404);
        } else if (time() > strtotime($contest['start_time'])) {
            throw new Exception('请在比赛开始之前注册比赛', 403);
        } else if (empty($login_data)) {
            throw new Exception('请先登录', 100);
        }

        $user = Oj::get_user_info_by_id($login_data['user_id']);
        $contest_user = Oj::get_contest_user($login_data['user_id'], $post_data['contest_id']);
        if (empty($user)) {
            throw new Exception('你所登录的用户不存在', 400);
        } else if (!empty($contest_user)) {
            throw new Exception('你已注册比赛,请尝试刷新页面', 400);
        }

        if ($contest['private']) {
            if (empty($post_data['contest_password'])) {
                throw new Exception('比赛密码不能为空', 100);
            } else if (!preg_match('/^\w+$/', $post_data['contest_password'])) {
                throw new Exception('比赛密码格式不正确', 100);
            } else if ($post_data['contest_password'] != $contest['password']) {
                throw new Exception('比赛密码错误', 400);
            }

        }

        if (Oj::insert_contest_user(['user_id' => $login_data['user_id'], 'contest_id' => $post_data['contest_id'], 'username' => $user['username'], 'nickname' => $user['nickname']])) {
            //注册成功
            return true;
        } else {
            throw new Exception('比赛注册失败,可能是内部原因', 500);
        }

    }

    public function execute() {

        $this->filter();

        $ret['register'] = true;
        return $ret;
    }

}
