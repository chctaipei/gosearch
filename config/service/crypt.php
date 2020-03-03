<?php
$config = $di->get("config");
$di->set(
    'crypt',
    function () use ($config) {
        $crypt = new \Phalcon\Crypt();
        // Use your own key!
        $encryptKey = $config->gosearch->application->encryptKey ?? $config->application->encryptKey;
        $crypt->setKey($encryptKey);
        return $crypt;
    }
);
