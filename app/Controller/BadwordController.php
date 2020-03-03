<?php

use Phalcon\Mvc\Controller;

/**
 * HotwordsController
 *
 * @RoutePrefix("/badword")
 */
class BadwordController extends Base
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
        $this->tag->prependTitle($this->config->gosearch->lang->sidebar->hotwords ?? $this->config->lang->sidebar->hotwords);
    }

    /**
     * indexAction
     *
     * @return   void
     *
     * Get("/project/{project}", name="badwords-project")
     * @Get("/", name="badwords-project")
     */
    public function indexAction()
    {
        parent::initialize();
        $project = $this->project;
        // $this->view->setVar('project', $project);
        $size = $this->request->get("size", ["trim", "int"], 100);

        $badwordList = $this->callTask("Tool", "listBadword", [$project, $size]);
        // $config = $this->callTask("Cron", "getProject", [$project, 'syncMatches']);
        // $this->view->setVar('type', $config['result']['data']['parameter']['type'] ?? '');
        // $this->view->setVar('scriptId', $config['result']['data']['parameter']['script'] ?? '');
        // $this->view->setVar('hotwordsJson', json_encode($hotList, JSON_NUMERIC_CHECK));
        $this->view->setVar('badwordList', $badwordList);
        $this->view->render("badword", "main");
    }
}
