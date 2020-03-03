<?php

use Phalcon\Mvc\Controller;

/**
 * LoginController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/login")
 */
class LoginController extends Base
{

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->tag->prependTitle($this->config->gosearch->lang->login->title ?? $this->config->lang->login->title);
    }

    /**
     * indexAction
     *
     * @return void
     *
     * @Get("/", name="login-page")
     */
    public function indexAction()
    {
        $this->setCsrf();
    }

    /**
     * loginAction
     *
     * @return void
     *
     * @Route("/", methods={"POST"}, name="login-submit")
     */
    public function loginAction()
    {
        if (!$this->checkToken()) {
            $this->setCsrf();
            $this->view->setVar("error", "驗證錯誤，請重新輸入");
            return $this->view->render('login', 'index');
        }

        $username = $this->request->getPost("username");
        $password = $this->request->getPost("password");

        $user = $this->callTask("Account", "getUser", [$username]);
        if ($user['status'] != 200) {
            $this->setCsrf();
            $this->view->setVar("error", "帳號未建立，請通知管理人員處理");
            $this->view->render('login', 'index');
            return;
        }

        $ret = $this->callTask("Account", "login", [$username, $password]);
        if ($ret['status'] == 200) {
            $this->session->set(
                'auth',
                [
                    'account' => $user['user']['account'],
                    'name'    => $user['user']['name'],
                    'level'   => $user['user']['level']
                ]
            );
            $url = "/";
            return $this->response->redirect($url);
        }

        $this->setCsrf();
        $this->view->setVar("error", "帳號或密碼錯誤，請重新輸入");
        $this->view->render('login', 'index');
    }
}
