<?php

require_once "../vendor/autoload.php";

define('ROOT_PATH', __DIR__);
//配置文件路径
define('CONF_DIR', __DIR__);
//配置文件类型
define('CONF_TYPE', 'YAML'); // 'YAML' 'PHP'
//配置文件名
define('ENV', 'env');

(new \Zero\Fpm\Entry())->run();
