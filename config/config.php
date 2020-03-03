<?php
date_default_timezone_set('Asia/Taipei');

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');

// $ENV = 'development';
$common = yaml_parse_file(__DIR__ . "/common.yml");
$tmp = $common['gosearch']['application'] ?? $common['application'];
foreach ($tmp as $key => $value) {
    if (!substr_compare($key, 'Dir', -3)) {
        // $tmp[$key] = realpath(__DIR__ . "/$value");
        if (isset($common['gosearch']['application'])) {
            $common['gosearch']['application'][$key] = realpath(__DIR__ . "/$value");
        } else {
            $common['application'][$key] = realpath(__DIR__ . "/$value");
        }
    }
}

$config = yaml_parse_file(__DIR__ . "/environment/" . APPLICATION_ENV .".yml");
if (isset($common['gosearch'])) {
    $config['gosearch'] = array_merge($common['gosearch'], $config['gosearch']);
} else {
    $config = array_merge($common, $config);
}

$lang = yaml_parse_file(__DIR__ . "/lang.yml");
if (isset($lang['gosearch'])) {
    $config['gosearch'] = array_merge($lang['gosearch'], $config['gosearch']);
} else {
    $config = array_merge($lang, $config);
}

$di->set("config", new \Phalcon\Config($config));
$loader = new \Phalcon\Loader();
$loader->registerDirs(
    [
     realpath(__DIR__ ."/../app/Controller/"),
     realpath(__DIR__ ."/../app/Controller/Api"),
    // realpath(__DIR__ ."/../task/"),
    ]
)->register();

require __DIR__ . "/service/logger.php";
if (PHP_SAPI !== 'cli') {
    include __DIR__ . "/service/router.php";
    include __DIR__ . "/service/dispatcher.php";
}
require __DIR__ . "/service/cache.php";
require __DIR__ . "/service/db.php";
require __DIR__ . "/service/request.php";
require __DIR__ . "/service/view.php";
require __DIR__ . "/service/url.php";
require __DIR__ . "/service/session.php";
require __DIR__ . "/service/crypt.php";
require __DIR__ . "/service/tag.php";
