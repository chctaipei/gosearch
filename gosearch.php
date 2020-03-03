<?php
require __DIR__.'/vendor/autoload.php';

use Phalcon\CLI\Console;
use Phalcon\DI\FactoryDefault\CLI;

$di = new CLI();

// load config
require __DIR__ . "/config/config.php";

try {
    \GoSearch\Helper\Message::setMessageLevel(0);
    // Create a console application
    $console = new Console();
    $console->setDI($di);
    define('TASK_NAMESPACE', 'GoSearch\\Task\\');
    if (!isset($argv[1])) {
        $argv[1] = "GoSearch\\Task\\Main";
    } else {
        $argv[1] = "GoSearch\\Task\\{$argv[1]}";
    }
    $console->setArgument($argv, false)->handle();
    // $console->handle(getArguments($argv));
} catch (\Phalcon\Cli\Dispatcher\Exception $ex) {
    echo "指令不存在, 請再確認...\n";
} catch (\Exception $ex) {
    throw $ex;
}

?>
