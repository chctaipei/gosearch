<?php

use Phalcon\Mvc\Controller;

/**
 * IndexController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/admin")
 */
class AdminController extends Base
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
    }

    /**
     * indexAction
     *
     * @return void
     *
     * @Get("/", name="admin-page")
     */
    public function indexAction()
    {
        $this->tag->prependTitle($this->config->gosearch->lang->sidebar->admin->title ?? $this->config->lang->sidebar->admin->title);
    }

    /**
     * projectsAction
     *
     * @return void
     *
     * @Get("/projects", name="admin-projects")
     */
    public function projectsAction()
    {
        $projects = array_keys($this->projects);
        // script
        foreach ($projects as $project) {
            $scriptInfo = $this->callTask("Search", "getScript", [$project]);
            $this->projects[$project]['scriptCount'] = count($scriptInfo['result']);
        }
        $this->view->setVar('projects', $this->projects);
    }

    /**
     * projectAction
     *
     * @return void
     *
     * @Get("/project", name="admin-project")
     */
    public function projectAction()
    {
        $type = $this->request->get("type", ["trim", "string"]);
        if ($type) {
            $this->view->pick("admin/document");
            return $this->documentAction($this->project, $type);
        }
        $this->view->pick("admin/projectdetail");
        return $this->projectDetailAction($this->project);
    }

    /**
     * projectDetailAction
     *
     * @param string $project project
     *
     * @return void
     *
     * @Get("/project?project={project}", name="admin-project-name")
     */
    public function projectDetailAction($project)
    {
        $this->view->setVar('project', $project);

        // indexInfo
        $indexInfo = $this->callTask("Index", "get", [$project]);

        // 設定 "前次上傳時間"
        foreach ($indexInfo['result'] as $key => $value) {
            if (isset($value['backups'])) {
                foreach ($value['backups'] as $key2 => $value2) {
                    list ($project1, $type1, $tid) = explode("_", $key2);
                    unset($value2, $project1);
                    if (isset($this->projects[$project]['data']['backup'][$type1][$tid])) {
                        $dateStr = date("Y-m-d H:i:s", $this->projects[$project]['data']['backup'][$type1][$tid]);
                        $indexInfo['result'][$key]['backups'][$key2]['importTime'] = $dateStr;
                    }
                }
            }
        }

        $this->view->setVar('indexInfo', $indexInfo['result']);

        // script
        $scriptInfo = $this->callTask("Search", "getScript", [$project]);
        $this->view->setVar('scriptInfo', $scriptInfo['result']);

        // jsonschema, uischema, type
        $schemaInfo = $this->callTask("Search", "getSchema", [$project]);
        $this->view->setVar('schemaInfo', $schemaInfo['result']);

        // projects
        $projects = $this->projects;

        // 替換掉 script and schema
        $projects[$project]['data']['search'] = $scriptInfo['result'];
        $projects[$project]['data']['schema'] = $schemaInfo['result'];
        $this->view->setVar('projects', $projects);
        $this->view->setVar('projectJson', json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

        // sourceInfo
        $sourceInfo = $projects[$project]['data']['source'] ?? [];
        $this->view->setVar('sourceInfo', $sourceInfo);

        // cronInfo
        $cronInfo = $this->callTask("Cron", "getProject", [$project]);
        $this->view->setVar('cronInfo', $cronInfo['result']);
        $this->view->setVar('cronInfoJson', json_encode($cronInfo['result'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

        // listDefault cron setting
        $cronDefault = $this->callTask("Cron", "listDefault");
        $this->view->setVar('cronDefaultJson', json_encode($cronDefault['result'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));

        // import plugins
        $filters = $this->callTask("Admin", "getImportFilters");
        $this->view->setVar('importFilters', $filters["filters"]);
    }

    /**
     * DocumentAction
     *
     * @param string $project project
     * @param string $type    type
     *
     * @return                                        void
     *
     * Get("/project/{project}/{type}", name="admin-project-doc")
     * @Get("/project?project={project}&type={type}", name="admin-project-doc")
     */
    public function documentAction($project, $type)
    {
        $this->view->setVar('project', $project);
        $this->view->setVar('type', $type);
        $indexInfo = $this->callTask("Index", "get", [$project, $type]);
        $mappingInfo = $indexInfo['result']["{$project}_{$type}"]['mappings'][$type]['properties'] ?? [];
        $this->view->setVar('mappingInfoJson', json_encode($mappingInfo, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    }

    /**
     * userAction
     *
     * @return void
     *
     * @Get("/users", name="admin-users")
     */
    public function userAction()
    {
        $this->loadUsers();
    }

    /**
     * systemAction
     *
     * @return void
     *
     * @Get("/service", name="admin-service")
     */
    public function serviceAction()
    {
        $cronInfo = $this->callTask("Cron", "status");
        $this->view->setVar('cronInfo', $cronInfo['result']);
    }
}
