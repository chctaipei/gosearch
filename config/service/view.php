<?php

$config = $di->get("config");
// Setting up the view component
$di->setShared(
    'view',
    function () use ($config, $di) {
        $view = new \Phalcon\Mvc\View();
        $viewDir = $config->gosearch->application->viewDir ?? $config->application->viewDir;
        $view->setViewsDir($viewDir);
        $view->registerEngines(
            [
                '.volt'  => function ($view, $di) use ($config) {
                    $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                    $cacheDir = $config->gosearch->application->cacheDir ?? $config->application->cacheDir;
                    $volt->setOptions(
                        [
                            'compiledPath'      => $cacheDir . "/",
                            'compiledSeparator' => '_',
                            'compileAlways'     => true
                        ]
                    );

                    $compiler = $volt->getCompiler();
                    $compiler->addFunction("echoActive", "echoActive");
                    /*
                        $compiler->addFunction("echoActive", function ($path) {
                        $comp1 = explode("/", $_SERVER['REQUEST_URI']);
                        $comp2 = explode("/", $path);
                        foreach ($comp2 as $key => $value) {
                            if (!isset($comp1[$key]) || $comp1[$key] != $value) {
                                return "";
                            }
                        }
                        return "active";
                        });
                    */
                    return $volt;
                },
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php',
            ]
        );

        return $view;
    }
);

/**
 * echoActive
 *
 * @param mixed $path path
 *
 * @return string
 */
function echoActive($path)
{
    $comp1 = explode("/", $_SERVER['REQUEST_URI']);
    $comp2 = explode("/", $path);
    foreach ($comp2 as $key => $value) {
        if (!isset($comp1[$key]) || $comp1[$key] != $value) {
            return "";
        }
    }
    return "active";
}
