<?php

use Phalcon\Mvc\Controller;

/**
 * LogoutController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/logout")
 */
class LogoutController extends Base
{

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 登出
     *
     * @return void
     *
     * @Get("/", name="logout-page")
     */
    public function indexAction()
    {
        $this->session->remove('auth');
        $this->response->redirect('/login');
        $this->view->disable();
    }
}
