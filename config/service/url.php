<?php
$di->set(
    'url',
    function () use ($config) {
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri('/');
        $url->setStaticBaseUri('/');
        return $url;
    }
);
