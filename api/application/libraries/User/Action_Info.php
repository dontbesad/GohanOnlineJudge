<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Action_Info {

    private function filter($user_id) {
        if (!isset($user_id)
            || !preg_match('/^\d+$/', $user_id)) {
            throw new Exception('用户不存在', 404);
        }
    }

    public function execute($user_id) {
        $this->filter($user_id);

        $user = Oj::get_user_info_by_id($user_id);
        if (empty($user)) {
            throw new Exception('用户不存在', 404);
        }

        $solved_problem = Oj::get_solved_problem_solution($user_id);
        $unsolved_problem = Oj::get_unsolved_problem_solution($user_id);

        $user['solved_problem']   = empty($solved_problem) ? [] : array_column($solved_problem, 'problem_id');
        $user['unsolved_problem'] = empty($unsolved_problem) ? [] : array_column($unsolved_problem, 'problem_id');
        foreach ($user['unsolved_problem'] as $index => $problem_id) {
            if (in_array($problem_id, $user['solved_problem'])) {
                unset($user['unsolved_problem'][$index]);
            }
        }

        if (empty($user)) {
            throw new Exception('用户不存在', 404);
        }

        return $user;
    }

}
