<?php

use Phalcon\Mvc\Controller;

/**
 * HotwordsController
 *
 * @RoutePrefix("/hotwords")
 */
class HotwordsController extends Base
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
     * Get("/project/{project}", name="hotwords-project")
     * @Get("/", name="hotwords-project")
     */
    public function indexAction()
    {
        parent::initialize();
        $project = $this->project;
        // $this->view->setVar('project', $project);
        $size = $this->request->get("size", ["trim", "int"], 1000);

        $hotList = $this->callTask("Search", "listHot", [$project, $size, "*", 0]);
        $config = $this->callTask("Cron", "getProject", [$project, 'syncMatches']);
        $this->view->setVar('type', $config['result']['data']['parameter']['type'] ?? '');
        $this->view->setVar('scriptId', $config['result']['data']['parameter']['script'] ?? '');

        // $this->view->setVar('hotwordsJson', json_encode($hotList, JSON_NUMERIC_CHECK));
        $this->view->setVar('hotwordsList', $hotList);
        $this->view->render("hotwords", "main");
    }
}
