<?php
require __DIR__ . "/../vendor/autoload.php";

$di = new Phalcon\DI\FactoryDefault();
new Whoops\Provider\Phalcon\WhoopsServiceProvider($di);

// load config
require __DIR__ . "/../config/config.php";

// Handle the request
$application = new \Phalcon\Mvc\Application($di);
echo $application->handle()->getContent();
?>
