<?php
$di->set(
    "request",
    function () {
        return new \Phalcon\Http\Request();
    },
    true
);
