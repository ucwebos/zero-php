<?php

require_once "../vendor/autoload.php";

define('ROOT_PATH', dirname(__DIR__));
define('CONF_TYPE', 'YAML'); // 'YAML' 'PHP'
define('CONF_DIR', dirname(__DIR__));

(new \Zero\Fpm\Entry())->run();
