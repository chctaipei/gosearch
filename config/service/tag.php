<?php
use Phalcon\Tag;

$config = $di->get("config");
$di->setShared(
    'tag',
    function () use ($config) {
        $tag = new Tag;

        $tag->setDocType(Tag::HTML5);
        $tag->setTitleSeparator(' - ');
        $tag->setTitle($config->gosearch->lang->title ?? $config->lang->title);

        return $tag;
    }
);
