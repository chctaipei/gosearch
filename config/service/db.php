<?php
$config = $di->get("config");

$di->set(
    "db",
    function () use ($config) {
        $db  = $config->gosearch->db ?? $config->db;
        $dsn = $db['dsn'];
        preg_match("/host=([^;]+)/", $dsn, $matches);
        $host = $matches[1];
        preg_match("/port=([^;]+)/", $dsn, $matches);
        $port = $matches[1];
        preg_match("/dbname=([^;]+)/", $dsn, $matches);
        $dbname = $matches[1];

        $dbCfg = [
            'host'     => $host,
            'dbname'   => $dbname,
            'port'     => $port,
            'charset'  => 'utf8',
            'username' => $db['user'],
            'password' => $db['password'],
        ];
        return new \Phalcon\Db\Adapter\Pdo\Mysql($dbCfg);
    }
);
