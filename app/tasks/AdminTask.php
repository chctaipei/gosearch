<?php
namespace GoSearch\Task;

use GoSearch\Admin;
use GoSearch\Project;
use GoSearch\ProjectSearch;
use GoSearch\Plugin\Handler;

/**
 * AdminTask
 *
 * @subject("專案建立及設定")
 *
 * @author("hc_chien <hc_chien>")
 */
class AdminTask extends MainTask
{

    /**
     * init config
     *
     * @return void
     *
     * @subject("系統設定初始化")
     */
    public function initSystemAction()
    {
        $admin = new Admin();
        $admin->initSystem();
        return $this->response(200, "完成系統初始化");
    }

    /**
     * init project
     *
     * @param array $params parameter
     *
     * @return void
     *
     * @subject("根據 config/project/project.yml 對專案做初始化建置")
     *
     * @arg("[PROJECT] [JSON/FILE]")
     */
    public function initProjectAction($params)
    {
        $project = $params[0] ?? null;
        $data    = null;
        if (isset($params[1])) {
            $data = $this->fetchParam($params[1]);
            if (!$data) {
                return $this->response(400, "json 格式錯誤\n");
            }
        }

        $admin = new Admin();
        $result = $admin->initProject($project, $data);
        if ($result) {
            return $this->response(200, ["message" => "完成專案初始化"]);
        }

        return $this->response(400, "專案初始化失敗");
    }

    /**
     * list all Project
     *
     * @return void
     *
     * @subject("取得所有專案的設定")
     */
    public function listProjectsAction()
    {
        try {
            $projectObj = new Project();
            $ret        = $projectObj->getProjects(0, 1000);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "系統尚未完成初始化");
        }

        if ($ret['total']) {
            return $this->response(200, ['projects' => $ret['hits']]);
        }
        return $this->response(404, "專案不存在");
    }

    /**
     * show Project config
     *
     * @param array $params parameter
     *
     * @return void
     *
     * @subject("取得專案的設定")
     *
     * @arg("[PROJECT]")
     */
    public function showProjectAction($params)
    {
        if (!isset($params[0])) {
            return $this->listProjectsAction();
        }

        try {
            $projectObj = new Project();
            $ret        = $projectObj->getProject($params[0]);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "系統尚未完成初始化");
        }

        if ($ret) {
            return $this->response(200, ['data' => $ret]);
        }
        return $this->response(404, "專案不存在");
    }

    /**
     * del Project config
     *
     * @param array $params parameter
     *
     * @return void
     *
     * @subject("刪除專案目前的設定, 不會清除 index 及 table")
     *
     * @arg("[PROJECT]")
     */
    public function deleteProjectAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $admin = new Admin();
        try {
            if ($admin->deleteProject($params[0])) {
                return $this->response(200, ['message' => '成功刪除專案']);
            } else {
                return $this->response(500, ['message' => '刪除失敗，專案不存在？']);
            }
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "系統尚未完成初始化");
        }
    }

    /**
     * update project index setting
     *
     * @param array $params params
     *
     * @return void
     *
     * @subject("新增或修改 type mapping - 參考: productIndex.json")
     *
     * @arg("PROJECT TYPE JSON/FILE")
     */
    public function setIndexAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $data = $this->fetchParam($params[2]);
        if (!$data && $params[2] !== "") {
            return $this->response(400, "json 格式錯誤\n");
        }

        // 刪除內容為空的 tag
        if ($params[2] != "") {
            $keys = array_keys($data);
            foreach ($keys as $key) {
                if (empty($data[$key])) {
                    unset($data[$key]);
                }
            }
        }

        try {
            $project = new Project($params[0]);
            $ret     = $project->setIndex($params[1], $data);
            if ($ret) {
                return $this->response(200,  "更新成功");
            }
            return $this->response(400,  "更新失敗");
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400,  "未完成初始化, 請先執行 initSystem\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * setSyncAction
     * 這個不再使用
     *
     * @param array $params params
     *
     * @return void
     *
     * subject("設定熱門關鍵字同步用的 search schema name")
     *
     * arg("PROJECT SCHEMANAME")
     */
    public function setSyncAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        try {
            $project = new Project($params[0]);
            $message = $project->setSync($params[1]);
            return $this->response(200, $message);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "完成初始化, 請先執行 initSystem\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * setSource
     *
     * @param array $params params
     *
     * @return void
     *
     * @subject("新增資料來源")
     *
     * @arg("PROJECT SOURCENAME JSON/FILE")
     */
    public function setSourceAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $data = $this->fetchParam($params[2]);
        if (!$data && $params[2] !== "") {
            return $this->response(400, "json 格式錯誤\n");
        }

        try {
            $project = new Project($params[0]);
            $message = $project->setSource($params[1], $data);
            return $this->response(200, $message);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "完成初始化, 請先執行 initSystem\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * setImport
     *
     * @param array $params params
     *
     * @return void
     *
     * @subject("設定 index 與資料來源的對應關係")
     *
     * @arg("PROJECT TYPE SOURCENAME")
     */
    public function setImportAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        try {
            $project = new Project($params[0]);
            $ret = $project->getProject();
            if (!isset($ret['index'][$params[1]])) {
                return $this->response(400, "索引 {$params[1]} 不存在\n");
            }
            if (!isset($ret['source'][$params[2]])) {
                return $this->response(400, "資料源 {$params[2]} 不存在\n");
            }
            $message = $project->setImport($params[1], $params[2]);
            return $this->response(200, $message);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * setBackups
     *
     * @param array $params params
     *
     * @return void
     *
     * @subject("設定 index 的備分數")
     *
     * @arg("PROJECT TYPE BACKUP")
     */
    public function setBackupAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        if ($params[2] < 0 || $params[2] > 10) {
            return $this->response(400, "輪替數量需介於0~10 (0=取消輪替)\n");
        }

        try {
            $project = new Project($params[0]);
            $ret = $project->getProject();
            if (!isset($ret['index'][$params[1]])) {
                return $this->response(400, "索引 {$params[1]} 不存在\n");
            }

            $message = $project->setBackup($params[1], $params[2]);
            return $this->response(200, $message);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * Get import plugins
     * Usage:
     * $className = "GoSearch\\Plugin\\" . $plugins[0];
     * $a = new $className();
     * $a->filter([]);
     *
     * @return array
     *
     * @subject("取得 import 可以套用的 filter")
     */
    public function getImportFiltersAction()
    {
        $handler = new Handler();
        $plugins = $handler->getImportFilters();
        return $this->response(200, ["filters" => $plugins]);
    }
}
