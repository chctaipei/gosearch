<?php
$di->set(
    'dispatcher',
    function () {
        $eventsManager = new \Phalcon\Events\Manager();
        $eventsManager->attach(
            'dispatch',
            function ($event, $dispatcher, $exception) {
                if ($event->getType() == 'beforeNotFoundAction') {
                        $dispatcher->forward(
                            [
                            'controller' => 'error',
                            'action'     => 'show404',
                            ]
                        );

                        return false;
                }
                if ($event->getType() == 'beforeException') {
                    switch ($exception->getCode()) {
                        case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                        case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                            $dispatcher->forward(
                                [
                                'controller' => 'error',
                                'action'     => 'show404',
                                ]
                            );

                            return false;
                    }
                }
            }
        );

        $dispatcher = new \Phalcon\Mvc\Dispatcher();
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }
);
