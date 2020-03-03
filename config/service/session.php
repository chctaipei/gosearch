<?php
// use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Session\Adapter\Redis as Session;

$config = $di->get("config");

$di->setShared(
    "session",
    function () use ($config) {
        $session = new Session(
            [
                'host'       => $config->gosearch->redis->host ?? $config->redis->host,
                'port'       => $config->gosearch->redis->port ?? $config->redis->port,
                'persistent' => true,
                'index'      => $config->gosearch->redis->db ?? $config->redis->db,
                'auth'       => $config->gosearch->redis->pwd ?? $config->redis->pwd ?? '',
                'prefix'     => 'user'
            ]
        );

        $session->start();
        return $session;
    }
);
