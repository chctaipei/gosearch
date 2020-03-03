<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

/**
 * Class: Base
 *
 * @see Controller
 */
class Base extends Controller
{
    protected $projects = [];
    protected $scripts  = [];
    protected $users    = [];
    protected $project  = [];
    protected $auth;

    /**
     * setCsrf
     *
     * @return void
     */
    protected function setCsrf()
    {
        $this->view->setVars(
            [
                'csrfkey'   => $this->security->getTokenKey(),
                'csrftoken' => $this->security->getToken(),
            ]
        );
    }

    /**
     * checkToken
     *
     * @return boolean
     */
    public function checkToken()
    {
        return $this->security->checkToken();
    }

    /**
     * loadProjects
     *
     * @return void
     */
    public function loadProjects()
    {
        if (!empty($this->projects)) {
            return;
        }

        $ret = $this->callTask("Admin", "listProjects");
        if ($ret['status'] == 200 && count($ret['projects'])) {
            foreach ($ret['projects'] as $project) {
                $this->projects[$project['name']] = $project;
            }
        }
        $this->view->setVar('projects', $this->projects);
        $this->view->setVar('projectJson', json_encode($this->projects, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    }

    /**
     * load search scripts
     *
     * @return void
     */
    public function loadScripts()
    {
        if (!empty($this->scripts)) {
            return;
        }

        $ret = $this->callTask("Search", "getScript", ["*"]);
        if ($ret['status'] == 200 && count($ret['result'])) {
            $this->scripts = $ret['result'];
        }
        $this->view->setVar('scripts', $this->scripts);
    }

    /**
     * checkSchema 檢查索引的狀態
     *
     * @return void
     */
    public function checkSchema()
    {
        $ret = [];
        $this->loadProjects();
        foreach ($this->projects as $project) {
            if (!isset($project['data']['index'])) {
                $project['data']['index'] = [];
            }

            foreach ($project['data']['index'] as $type => $schema) {
                unset($schema);
                $ret[$project['name']][$type] = $this->callTask("Index", "check", [$project['name'], $type]);
            }
        }
        $this->view->setVar('indexcheckJson', json_encode($ret, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    }

    /**
     * loadUsers
     *
     * @return void
     */
    public function loadUsers()
    {
        if (!empty($this->users)) {
            return;
        }

        $ret = $this->callTask("Account", "listUsers");
        if ($ret['status'] == 200 && count($ret['users'])) {
            foreach ($ret['users'] as $user) {
                $this->users[$user['account']] = $user;
            }
        }

        $this->view->setVar('users', $this->users);
    }

    /**
     * initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->view->setVar('text', $this->config->gosearch->lang ?? $this->config->lang);
        $this->selectProject();
    }

    /**
     * isUserAuthenticated
     *
     * @return boolean
     */
    public function isUserAuthenticated()
    {
        $auth = $this->session->get("auth");
        if (!$auth) {
            return false;
        }

        $this->auth = $auth;
        $this->view->setVar('auth', $auth);
        return true;
    }

    /**
     * select project
     *
     * @param string $project project
     *
     * @return boolean
     */
    public function selectProject($project = null)
    {
        $project = $project ?? $this->request->get("project") ?? $this->session->get("project");

        if (!$project) {
            return false;
        }

        $this->project = $project;
        $this->session->set("project", $project);
        $this->view->setVar('project', $project);
        return true;
    }

    /**
     * checkAuth 檢查權限 TODO 未來對個別專案設定權限
     *
     * @param int $level level
     *
     * @return boolean
     */
    public function checkAuth($level = 0)
    {
        if (!$this->isUserAuthenticated()) {
            $this->jsonOutput(["status" => 401, "message" => "尚未登入"]);
            return false;
        }

        // 等級調整後，需要重新登入
        if ($this->auth['level'] > $level) {
            $this->jsonOutput(["status" => 401, "message" => "權限不足"]);
            return false;
        }

        return true;
    }

    /**
     * callTask
     *
     * @param string $task      task
     * @param string $action    action
     * @param array  $parameter parameter
     *
     * @return array
     */
    public function callTask($task, $action, $parameter = [])
    {
        return \GoSearch\Helper\Task::callTask($task, $action, $parameter);
    }

    /**
     * jsonOutput
     *
     * @param array $ret ret
     *
     * @return void
     */
    public function jsonOutput($ret)
    {
        $response = new Response();

        if (isset($ret['status'])) {
            $response->setStatusCode($ret['status'], "");
        }

        $response->setContentType('application/json', 'UTF-8');
        $response->setContent(json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response->send();
    }
}
