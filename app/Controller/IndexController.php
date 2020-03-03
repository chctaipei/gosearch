<?php

use Phalcon\Mvc\Controller;

/**
 * IndexController
 */
class IndexController extends Base
{

    /**
     * beforeExecuteRoute
     *
     * @return boolean
     */
    public function beforeExecuteRoute()
    {
        if (!$this->isUserAuthenticated()) {
            $this->response->redirect('/login');
            $this->view->disable();
            return false;
        }

        return true;
    }

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize()
    {
        $this->loadProjects();
        $this->loadScripts();
        parent::initialize();
        // $this->view->setVar('page_title', 'Dashboard');
    }

    /**
     * IndexAction
     *
     * @return void
     *
     * @Get("/", name="dashboard")
     */
    public function indexAction()
    {
        // $this->view->setVars(
        // ['page_subtitle' => 'Control panel']
        // );
    }
}
