<?php
use Phalcon\Cache\Frontend\Data as FrontendData;
use GoSearch\Redis as GoSearchRedis;

$config = $di->get("config");

// Set the models cache service
$di->set(
    "cache",
    function () use ($config) {
        // Cache data for one day by default
        $frontCache = new FrontendData(
            [
                "lifetime" => $config->gosearch->redis->lifetime ?? $config->redis->lifetime,
            ]
        );

        // Memcached connection settings
        // $cache = new BackendMemcache(
        $cache = new GoSearchRedis(
            $frontCache,
            [
                'host'       => $config->gosearch->redis->host ?? $config->redis->host,
                'port'       => $config->gosearch->redis->port ?? $config->redis->port,
                'persistent' => true,
                'index'      => $config->gosearch->redis->db ?? $config->redis->db,
                'auth'       => $config->gosearch->redis->pwd ?? $config->redis->pwd ?? ''
                // 'statsKey'   => '_PHCR'
            ]
        );

        return $cache;
    }
);
