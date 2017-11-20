<?php
require_once 'database.php';

$config_str = file_get_contents('./gohan.json');
$config_arr = json_decode($config_str, true);

$compiler_cmd = $config_arr['compiler'] . ' ' . $config_arr['compile_time'] . ' Main ';

$judger_cmd   = '';
//$comparer_cmd = ;
