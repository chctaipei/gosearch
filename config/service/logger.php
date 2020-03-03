<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler ;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FluentdFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogglyFormatter;

$config = $di->get("config");

// Set the models cache service
$di->set(
    "logger",
    function () use ($config) {
        $logger = new Logger('');
        $logDir = $config->gosearch->application->logDir ?? $config->application->logDir;
        $logFile = $config->gosearch->log->file ?? $config->log->file;
        $filename = $logDir ."/". $logFile;
        // $handler = new StreamHandler($filename, Logger::DEBUG, true, 0666);
        $handler = new RotatingFileHandler($filename, 30, Logger::DEBUG, true, 0666);
        $logFormat = $config->gosearch->log->format ?? $config->log->format;
        $formatter = new LineFormatter($logFormat);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return $logger;
    }
);
