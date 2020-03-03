<?php

use Phalcon\Mvc\Controller;

/**
 * ErrorController
 */
class ErrorController extends Base
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
     * notFoundAction
     *
     * @return void
     */
    public function notFoundAction()
    {
        // $params = $this->dispatcher->getParams();
        return $this->jsonOutput(["status" => 400, "message" => "錯誤的指令"]);
    }
}
