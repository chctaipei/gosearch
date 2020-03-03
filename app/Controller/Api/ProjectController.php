<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * ProjectController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/api/project")
 */
class ProjectController extends \Base
{

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * beforeExecuteRoute
     *
     * @return boolean
     */
    public function beforeExecuteRoute()
    {
        return $this->checkAuth();
    }

    /**
     * [GET] 取得 project
     *
     * @param string $project project
     *
     * @return void
     *
     * @Get("/{project}", name="api-get-project")
     */
    public function getAction($project)
    {
        return $this->jsonOutput($this->callTask("Admin", "showProject", [$project]));
    }

    /**
     * [POST] 新增 project
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}", methods={"POST"})
     */
    public function createAction($project)
    {
        return $this->jsonOutput($this->callTask("Admin", "initProject", [$project]));
    }

    /**
     * [PUT] project script 更新及改名
     *
     * @param string $project  project
     * @param string $scriptId scriptId
     *
     * @return void
     *
     * @Route("/{project}/script/{scriptId}", methods={"PUT"})
     */
    public function updateScriptAction($project, $scriptId)
    {
        $value = $this->request->getJsonRawBody(true);

        // rename 是保留字
        if (isset($value['rename'])) {
            $newScriptId = $value['rename'];
            return $this->jsonOutput($this->callTask("Search", "renameScript", [$project, $scriptId, $newScriptId]));
        }

        return $this->executeAction("Search", "putScript", $project, $value);
    }

    /**
     * [PUT] project schema 更新及改名
     *
     * @param string $project  project
     * @param string $scriptId scriptId
     *
     * @return void
     *
     * @Route("/{project}/schema/{scriptId}", methods={"PUT"})
     */
    public function updateSchemaAction($project, $scriptId)
    {
        $value = $this->request->getJsonRawBody(true);

        // rename 是保留字
        if (isset($value['rename'])) {
            $newScriptId = $value['rename'];
            return $this->jsonOutput($this->callTask("Search", "renameSchema", [$project, $scriptId, $newScriptId]));
        }

        return $this->executeAction("Search", "putSchema", $project, $value);
    }

    /**
     * executeAction
     *
     * @param string $task    task
     * @param string $action  action
     * @param string $project project
     * @param array  $data    data
     *
     * @return void
     */
    private function executeAction($task, $action, $project, $data)
    {
        if (count($data) > 1 && $action == "putScript") {
            return $this->handleScriptRename($project, $data);
        }

        $type  = key($data);
        $value = $data[$type];
        return $this->jsonOutput($this->callTask($task, $action, [$project, $type, $value]));
    }

    /**
     * [PUT] 修改 project config
     *
     * @param string $project project
     * @param string $config  config
     *
     * @return void
     *
     * @Route("/{project}/config/{config}", methods={"PUT"})
     */
    public function putConfigAction($project, $config)
    {
        $value = $this->request->getJsonRawBody(true);

        switch ($config) {
            case 'sync':
                return $this->jsonOutput($this->callTask("Admin", "setSync", [$project, $value]));
            case 'index':
                return $this->executeAction("Admin", "setIndex", $project, $value);
            case 'search':
                return $this->executeAction("Search", "putScript", $project, $value);
            case 'schema':
                return $this->executeAction("Search", "putSchema", $project, $value);
            case 'setting':
                return $this->executeAction("Admin", "setSetting", $project, $value);
            case 'source':
                return $this->executeAction("Admin", "setSource", $project, $value);
            case 'import':
                return $this->executeAction("Admin", "setImport", $project, $value);
            case 'backup':
                return $this->executeAction("Admin", "setBackup", $project, $value);
            case 'cronjob':
                $task  = $value['task'];
                $param = json_encode($value['param']);
                $cronstring = $value['cronstring'];
                $type  = $value['type'];
                return $this->jsonOutput(
                    $this->callTask(
                        "Cron",
                        "updateProjectJob",
                        [$project, $task, $param, $cronstring, $type]
                    )
                );
            default:
                return $this->jsonOutput(['status' => 403, 'message' => "config:$config is not accepted"]);
        }//end switch
    }

    /**
     * [PUT] 修改 project conjob
     *
     * @param string $project project
     * @param string $task    task
     *
     * @return void
     *
     * @Route("/{project}/cronjob/{task}", methods={"PUT"})
     */
    public function putCronjobAction($project, $task)
    {
        $value = $this->request->getJsonRawBody(true);
        $param = json_encode($value['param']);
        $cronstring = $value['cronstring'];
        $type  = $value['type'];
        return $this->jsonOutput($this->callTask("Cron", "updateProjectJob", [$project, $task, $param, $cronstring, $type]));
    }

    /**
     * [POST] 執行 project conjoob
     * 省略 type 的檢查，由 jobId 確認
     *
     * @param string $project project
     * @param string $task    task
     *
     * @return void
     *
     * @Route("/{project}/cronjob/{task}/run", methods={"POST"})
     */
    public function runCronjobAction($project, $task)
    {
        $value = $this->request->getJsonRawBody(true);
        $jobId = $value['jobid'];
        $result = $this->callTask("Cron", "getJob", [$jobId]);

        if (!isset($result['result']) || empty($result['result'])) {
            return $this->jsonOutput(['status' => 404, 'message' => "jobId:$jobId not found"]);
        }

        if ($result['result']['task'] != $task || $result['result']['jobid'] != $jobId) {
            return $this->jsonOutput(['status' => 400, 'message' => "task or jobid not match"]);
        }

        if ($project != $result['result']['project']) {
            return $this->jsonOutput(['status' => 400, 'message' => "專案比對不符"]);
        }

        $status = $result['result']['status'];
        if ($status == \GoSearch\Cronjob::STATUS_INSTANT) {
            return $this->jsonOutput(['status' => 400, 'message' => "已排定"]);
        }

        if ($status == \GoSearch\Cronjob::STATUS_RUNNING) {
            return $this->jsonOutput(['status' => 400, 'message' => "執行中"]);
        }

        return $this->jsonOutput($this->callTask("Cron", "runJob", [$jobId]));
    }

    /**
     * [GET|POST] get job by jobId
     *
     * @param string $project project
     * @param int    $jobId   jobId
     *
     * @return void
     *
     * @Route("/{project}/job/{jobId}", methods={"POST","GET"})
     */
    public function getCronjobAction($project, $jobId)
    {
        // 用 GET 不會送 body, 要改用 POST
        $value = $this->request->getJsonRawBody(true);
        $offset = $value['offset'] ?? 0;

        $result = $this->callTask("Cron", "getJob", [$jobId, $offset]);

        if (!isset($result['result']) || empty($result['result'])) {
            return $this->jsonOutput(['status' => 404, 'message' => "jobId:$jobId not found"]);
        }

        if ($project != $result['result']['project']) {
            return $this->jsonOutput(['status' => 400, 'message' => "專案比對不符"]);
        }

        return $this->jsonOutput($result);
    }

    /**
     * [PUT] 開關 conjob
     *
     * @param string $project project
     * @param string $task    task
     *
     * @return void
     *
     * @Route("/{project}/cronjob/{task}/active", methods={"PUT"})
     */
    public function activeCronjobAction($project, $task)
    {
        $value = $this->request->getJsonRawBody(true);
        $active = $value['active'];
        $type  = $value['type'];
        return $this->jsonOutput($this->callTask("Cron", "activeProject", [$project, $task, $active, $type]));
    }

    /**
     * [DELETE] 刪除 project
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}", methods={"DELETE"})
     */
    public function delAction($project)
    {
        $username = $this->auth['account'];
        $ret = $this->request->getJsonRawBody(true);
        $password = $ret['password'];

        $ret = $this->callTask("Account", "login", [$username, $password]);
        if ($ret['status'] != 200) {
            return $this->jsonOutput($ret);
        }

        return $this->jsonOutput($this->callTask("Admin", "deleteProject", [$project]));
    }

    /**
     * [PUT] 更新/建立 index mapping schema
     *
     * @param string $project project
     * @param string $type    type
     *
     * @return void
     *
     * @Route("/{project}/mapping/{type}", methods={"PUT"}, name="api-create-index")
     */
    public function createMappingAction($project, $type)
    {
        return $this->jsonOutput($this->callTask("Index", "create", [$project, $type]));
    }

    /**
     * [DELETE] 刪除 index
     *
     * @param string $project project
     * @param string $type    type
     *
     * @return void
     *
     * @Route("/{project}/mapping/{type}", methods={"DELETE"}, name="api-delete-index")
     */
    public function deleteMappingAction($project, $type)
    {
        return $this->jsonOutput($this->callTask("Index", "delete", [$project, $type]));
    }

    /**
     * [POST] 切換 Index alias
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}/alias", methods={"POST"}, name="api-switch-alias")
     */
    public function switchAliasAction($project)
    {
        $value = $this->request->getJsonRawBody(true);
        $type = $value['type'];
        $tid = $value['id'];
        return $this->jsonOutput($this->callTask("Index", "switchAlias", [$project, $type, $tid]));
    }
}
