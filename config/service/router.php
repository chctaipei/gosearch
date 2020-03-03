<?php
use Phalcon\Mvc\Router\Annotations;

use Phalcon\Mvc\Router;

$config = $di->get("config");

$di->setShared(
    "router",
    function () use ($di, $config) {
        $em = $this->getShared('eventsManager');

        $router = new Annotations(false);
        $router->removeExtraSlashes(true);

        // @todo Use Path::normalize()
        $controllersDir = $config->gosearch->application->controllerDir ?? $config->application->controllerDir;

        $resources = getResource($controllersDir);

        foreach ($resources as $controller) {
            $router->addResource($controller);
        }

        $router->setEventsManager($em);
        $router->setDefaultAction('index');
        $router->setDefaultController('index');
        // $router->setDefaultNamespace('');
        // $router->notFound(['controller' => 'error', 'action' => 'route404']);
        $router->notFound(['controller' => 'Error', 'action' => 'notFound']);

        return $router;
    }
);

/**
 * 每個子目錄要補上 className
 *
 * @param string $controllersDir controllersDir
 * @param string $class          class
 *
 * @return array
 */
function getResource($controllersDir, $class = "\\")
{
    $resources = [];

    $dir = new DirectoryIterator($controllersDir);
    foreach ($dir as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }

        if ($fileInfo->isDir()) {
            $childClass = $fileInfo->getBasename() . "\\";
            $resources = array_merge($resources, getResource($fileInfo->getPathname(), $class . $childClass));
            continue;
        }

        if (false === strpos($fileInfo->getBasename(), 'Controller.php')) {
            continue;
        }

        if (strstr($fileInfo->getBasename('Controller.php'), '.')) {
            continue;
        }

        $controller = $fileInfo->getBasename('Controller.php');
        $resources[] = $class . $controller;
    }//end foreach

    return $resources;
}
