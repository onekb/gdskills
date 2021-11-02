<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

require 'vendor/autoload.php';

$main = new \Onekb\Gdskills\Main();

$main->run();
