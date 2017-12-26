SET AUTOCOMMIT=0;
BEGIN;

set names utf8;
create database oj;
use oj;
set names utf8;

CREATE TABLE `sys_solution` (
    `solution_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `source_code` TEXT,
    `problem_id`  INT NOT NULL DEFAULT 0,
    `user_id`     INT NOT NULL DEFAULT 0,
    `contest_id`  INT DEFAULT 0, #默认无关比赛
    `runtime`     INT DEFAULT 0,
    `memory`      INT DEFAULT 0,
    `result`      INT(4) NOT NULL DEFAULT 0, #默认在队列中
    `error`       TEXT, #RE信息
    `submit_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, #默认当前时间
    `code_length` INT NOT NULL DEFAULT 0,
    `language`    INT(4) NOT NULL DEFAULT 1, #默认c语言
    `valid`       INT(2) DEFAULT 1 #默认有效(显示)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `sys_solution` ADD INDEX i_problem_id(`problem_id`);
ALTER TABLE `sys_solution` ADD INDEX i_user_id(`user_id`);
ALTER TABLE `sys_solution` ADD INDEX i_contest_id(`contest_id`);
ALTER TABLE `sys_solution` ADD INDEX i_problem_id(`problem_id`);
ALTER TABLE `sys_solution` ADD INDEX i_result(`result`);
ALTER TABLE `sys_solution` ADD INDEX i_language(`language`);


CREATE TABLE `sys_problem` (
    `problem_id`    INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`         VARCHAR(200) DEFAULT '',
    `description`   TEXT,
    `input`         TEXT,
    `output`        TEXT,
    `sample_input`  TEXT,
    `sample_output` TEXT,
    `hint`          TEXT,
    `source`        VARCHAR(200) DEFAULT '',
    `time_limit`    INT NOT NULL DEFAULT 1000,  #1000MS
    `memory_limit`  INT NOT NULL DEFAULT 32678, #32678KB 32M
    `accepted_num`  INT DEFAULT 0,
    `solved_num`    INT DEFAULT 0, #解决数小于等于ac数
    `submit_num`    INT DEFAULT 0,
    `spj`           INT(2) DEFAULT 0,
    `visible`       INT(2) DEFAULT 0 #默认题目列表中不可见
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `sys_problem` AUTO_INCREMENT=1000;


CREATE TABLE `sys_contest` (
    `contest_id`  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(200) NOT NULL DEFAULT '',
    `description` TEXT, #比赛描述
    `start_time`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `end_time`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `private`     INT(2) DEFAULT 0, #默认公开
    `password`    VARCHAR(100) NOT NULL DEFAULT '', #私有比赛的密码
    `rated`       INT(2) DEFAULT 0 #不算分数的比赛
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `sys_contest_problem` (
    `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `contest_id`    INT NOT NULL DEFAULT 0,
    `problem_id`    INT NOT NULL DEFAULT 0,
    `order_id`      VARCHAR(20) NOT NULL DEFAULT '', #对应比赛中的顺序id(A、1001)
    `title`         VARCHAR(200) NOT NULL DEFAULT '', #比赛中题目的标题
    `accepted_num`  INT DEFAULT 0,
    `solved_num`    INT DEFAULT 0, #解决数小于等于ac数
    `submit_num`    INT DEFAULT 0,
    `score`         INT DEFAULT 0 #默认分数0，以后有空写rating系统
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `sys_contest_problem` ADD INDEX i_contest(`contest_id`, `problem_id`);


CREATE TABLE `sys_user` (
    `user_id`     INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username`    VARCHAR(20) NOT NULL UNIQUE, #用于用户登录名，可以修改，但是不能重名
    `password`    VARCHAR(250) NOT NULL DEFAULT '',
    `reg_time`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    `school`      VARCHAR(100) DEFAULT '',
    `email`       VARCHAR(50) DEFAULT '',
    `reg_ip`      VARCHAR(20)  DEFAULT '',
    `description` VARCHAR(200) DEFAULT '',
    `valid`       INT(2) DEFAULT 1 #默认有效
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `sys_user` ADD INDEX i_school(`school`);


#私有比赛注册用户
CREATE TABLE `sys_contest_user` (
    `id`         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `contest_id` INT NOT NULL DEFAULT 0,
    `user_id`    INT NOT NULL DEFAULT 0
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `sys_contest_user` ADD INDEX i_contest_user(`contest_id`, `user_id`);

-- CREATE TABLE `sys_log_login` (
--     `id`       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     `user_id`  INT NOT NULL DEFAULT 0,
--     `ip`       VARCHAR(20) DEFAULT '',
--     `log_time` DATETIME DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- ALTER TABLE `sys_log_login` ADD INDEX i_user_id(`user_id`);

CREATE TABLE `cfg_user_role` (
    `id`      INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL DEFAULT 0,
    `role_id` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `cfg_user_role` ADD INDEX i_user_id(`user_id`);
ALTER TABLE `cfg_user_role` ADD INDEX i_role_id(`role_id`);

CREATE TABLE `cfg_role` (
    `role_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`    VARCHAR(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `cfg_role_rule` (
    `id`      INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL DEFAULT 0,
    `rule_id` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `cfg_role_rule` ADD INDEX i_role_id(`role_id`);
ALTER TABLE `cfg_role_rule` ADD INDEX i_rule_id(`rule_id`);

CREATE TABLE `cfg_rule` (
    `rule_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `class`   VARCHAR(20) NOT NULL DEFAULT '',
    `method`  VARCHAR(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
ALTER TABLE `cfg_rule` ADD INDEX i_rule(`class`, `method`);

COMMIT;
SET AUTOCOMMIT=1;


-- cfg_language, cfg_result
-- sys_solution - result:
-- Accepted 1
-- Wrong Answer 2
-- Presentation Error 3
-- Output Limit Exceeded 4
-- Time Limit Exceeded 5
-- Memory Limit Exceeded 6
-- Runtime Error 7
-- Malicious Code 8
-- Compilation Error 9 #包含Compilation Time Limimt Exceeded(May contain '/dev/random' header files)的情况
-- System Error 10
-- Queuing 0
-- Compiling 11
-- Running 12
