<?php

use Phalcon\Mvc\Controller;

/**
 * SearchController
 *
 * @RoutePrefix("/search")
 */
class SearchController extends Base
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
        $this->tag->prependTitle($this->config->gosearch->lang->sidebar->search ?? $this->config->lang->sidebar->search);
    }

    /**
     * indexAction
     *
     * @return void
     *
     * @Get("/", name="search-list")
     */
    public function indexAction()
    {
        parent::initialize();
    }

    /**
     * getScripts
     *
     * @param string $project  project
     * @param string $scriptId scriptId
     *
     * @return void
     */
    private function getScripts($project, $scriptId = '')
    {
        $this->view->setVar('project', $project);
        $this->view->setVar('scriptId', $scriptId);

        // indexInfo
        $indexInfo = $this->callTask("Index", "get", [$project]);
        $this->view->setVar('indexInfo', $indexInfo['result']);

        if ($scriptId) {
            $this->view->setVar('scriptId', $scriptId);
            $result = $this->callTask("Search", "getSchema", [$project, $scriptId]);
            $schemaInfo = $result['result'];

            $this->view->setVar(
                'schemaInfoJson',
                json_encode($schemaInfo, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)
            );
            if (!isset($schemaInfo['type'])) {
                $schemaInfo['type'] = '';
            }
            $this->view->setVar('type', $schemaInfo['type']);
        }

        $scriptInfo = [];
        if (isset($this->scripts[$project][$scriptId])) {
            $scriptInfo = $this->scripts[$project][$scriptId];
        }
        $this->view->setVar('scriptInfoJson', json_encode($scriptInfo, JSON_NUMERIC_CHECK));
    }

    /**
     * scriptAction
     *
     * @param string $scriptId scriptId
     *
     * @return void
     *
     * Get("/project/{project}/{scriptId}", name="search-project-script")
     *
     * @Get("/{scriptId}", name="search-project-script")
     */
    public function scriptAction($scriptId)
    {
        $project = $this->project;
        $this->getScripts($project, $scriptId);
        $this->view->render("search", "main");
    }
}
