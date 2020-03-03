<?php
namespace GoSearch\Task;

use GoSearch\Project;
use GoSearch\ProjectIndex;
use GoSearch\Helper\Import;
use GoSearch\SearchClient;
use GoSearch\Importer;

/**
 * SearchTask
 *
 * @subject("索引")
 *
 * @author("hc_chien <hc_chien@hiiir.com>")
 */
class IndexTask extends MainTask
{

    /**
     * putTemplateAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("設定索引樣板")
     *
     * @arg("PROJECT TYPE SOURCE")
     */
    public function putTemplateAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $check = $params[1];

        if (!ctype_alnum($check)) {
            return $this->response(400, "請使用英數字\n");
        }

        try {
            $projectIndex = new ProjectIndex($params[0]);
            $data = $this->fetchParam($params[2], 0);
            $message = $projectIndex->putTemplate($params[1], $data);
            $message = $projectIndex->getTemplate($params[1]);
            return $this->response(200, $message);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "未完成初始化, 請先執行 initSystem\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * getTemplatetAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("取得索引樣板")
     *
     * @arg("PROJECT [TYPE]")
     */
    public function getTemplateAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $projectIndex = new ProjectIndex($params[0]);
        if (isset($params[1])) {
            $message = $projectIndex->getTemplate($params[1]);
            return $this->response(200, $message);
        }
        $result = $projectIndex->getAllTemplate();
        return $this->response(200, ['result' => $result]);
    }

    /**
     * deleteTemplateAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除索引樣板")
     *
     * @arg("PROJECT TYPE")
     */
    public function deleteTemplateAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $projectIndex = new ProjectIndex($params[0]);
        $message = $projectIndex->deleteTemplate($params[1]);
        $code = $message['status'] ?? 200;
        return $this->response($code, $message);
    }


    /**
     * show Project config
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("取得專案的索引設定")
     *
     * @arg("PROJECT [TYPE] [''|'index'|'backup'|'import']")
     */
    public function showAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params[0];
        $type    = $params[1] ?? null;
        $arg     = $params[2] ?? '';

        $projectObj = new Project();
        $data       = $projectObj->getProject($project, $arg);

        if ($arg == '') {
            if (!$type) {
                return $this->response(200, $data);
            }

            $ret['index'] = $data['index'][$type] ?? null;
            $ret['backup'] = $data['backup'][$type] ?? null;
            $ret['import'] = $data['import'][$type] ?? null;
            return $this->response(200, $ret);
        }

        if ($type) {
            if (isset($data[$type])) {
                return $this->response(200, $data[$type]);
            }
            return $this->response(404, null);
        }
        return $this->response(200, $data);
    }

    /**
     * 建立索引
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("建立索引 ('all' 表示全部)")
     *
     * @arg("[PROJECT] [TYPE]")
     */
    public function createAction($params)
    {
        $project = ProjectIndex::ALL_INDEX_TYPE;
        $type    = ProjectIndex::ALL_INDEX_TYPE;

        if (isset($params[0]) && $params[0] != 'all') {
            $project = $params[0];
        }

        if (isset($params[1]) && $params[1] != 'all') {
            $type = $params[1];
        }

        $indexHandler = new ProjectIndex($project);
        $result = $indexHandler->create($type);

        if ($project != ProjectIndex::ALL_INDEX_TYPE && $type != ProjectIndex::ALL_INDEX_TYPE) {
            if (isset($result['status']) && $result['status'] >= 400) {
                foreach ($result['message'] as $message) {
                    if (isset($message["mapping"]["error"]["reason"])) {
                        $result['message'][] = $message["mapping"]["error"]["reason"];
                    }
                }
            } else {
                $result['status'] = 200;
            }
        }
        return $this->response($result['status'], $result);
    }

    /**
     * 刪除所有的索引
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("刪除索引 ('all' 表示全部)")
     *
     * @arg("PROJECT TYPE")
     */
    public function deleteAction($params)
    {
        $project = ProjectIndex::ALL_INDEX_TYPE;
        $type    = ProjectIndex::ALL_INDEX_TYPE;

        if (!isset($params[1])) {
            return $this->response(400, "未指定刪除專案或索引");
        }

        if (isset($params[0]) && $params[0] != 'all') {
            $project = $params[0];
        }

        if (isset($params[1]) && $params[1] != 'all') {
            $type = $params[1];
        }

        $indexHandler = new ProjectIndex($project);
        $result = $indexHandler->delete($type);
        if ($result['status'] == 404) {
            return $this->response(404, "設定不存在或已經刪除");
        }

        return $this->response($result['status'], $result);
    }

    /**
     * 取得索引設定
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("取得索引設定 ('all' 表示全部)")
     *
     * @arg("[PROJECT] [TYPE]")
     */
    public function getAction($params)
    {
        $project = ProjectIndex::ALL_INDEX_TYPE;
        $type    = ProjectIndex::ALL_INDEX_TYPE;

        if (isset($params[0]) && $params[0] != 'all') {
            $project = $params[0];
        }

        if (isset($params[1]) && $params[1] != 'all') {
            $type = $params[1];
        }

        $indexHandler = new ProjectIndex();
        $result = $indexHandler->get($type, $project);

        return $this->response($result['status'], $result);
    }

    /**
     * 取得已建立的索引
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("取得已建立的專案索引, 可以用 *, 例如: '*' 'hot*'")
     *
     * @arg("[PROJECT] [TYPE]")
     */
    public function getCreatedMappingAction($params)
    {
        $project = '*';
        $type    = '*';
        if (isset($params[0])) {
            $project = $params[0];
            if (isset($params[1])) {
                $type = $params[1];
            }
        }
        $indexHandler = new ProjectIndex();
        $result = $indexHandler->getCreatedMapping($type, $project);

        return $this->response(200, $result);
    }

    /**
     * 檢查索引是否建立
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("檢查索引")
     *
     * @arg("PROJECT TYPE")
     */
    public function checkAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $indexHandler = new ProjectIndex($params[0]);
        $result = $indexHandler->checkMapping($params[1]);
        return $this->response($result['status'], $result['message']);
    }

    /**
     * import 文件 by 檔案
     * gohappy 與 superstore 的字串不處理:
     * explode('>',$data['FULL_CATEGORY_PATH']);
     * 這裡不用 fetchParam 因為檔案可能很大, 格式也要修整
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("從檔案輸入大筆資料 (ex: importFile gohappy product /tmp/xxx.json)")
     *
     * @arg("PROJECT TYPE JSON/FILE")
     */
    public function importFileAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params[0];
        $type    = $params[1];
        $file    = realpath($params[2]);
        $class   = "fetch$project$type";

        // 舊格式需要轉換
        //if (method_exists('\GoSearch\Helper\Import', $class)) {
        //    $feed = Import::$class($file);
        //} else {
            $feed = Import::fetchFile($file);
        //}

        $total = 0;
        $indexHandler = new ProjectIndex($project);

        foreach ($feed as $arr) {
            // \GoSearch\Helper\Message::debugLog($arr[0]['PRODUCT_ID']);
            $ret = $indexHandler->bulkIndex($arr, $type);
            if (isset($ret['errors'])) {
                \GoSearch\Helper\Message::debugLog("[importFileAction] error", $ret);
            }

            $total += count($arr);
            \GoSearch\Helper\Message::debugLog("[importFileAction] 已索引 $total 筆資料");
        }
        return $this->response(200, "索引完成\n");
    }

    /**
     * importAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("從 DB 餵入資料 (ex: import gohappy product)")
     *
     * @arg("PROJECT TYPE")
     */
    public function importAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params['project'] ?? $params[0];
        $type    = $params['type'] ?? $params[1];

        $projectObj = new Project();
        $ret        = $projectObj->getProject($project);

        if (!isset($ret['import'][$type])) {
            return $this->response(400, "未設定 import 來源\n");
        }

        $source = $ret['import'][$type];

        if (!isset($ret['source'][$source])) {
            return $this->response(400, "db 來源: $source 不存在\n");
        }

        \GoSearch\Helper\Message::debugLog("[importAction] project = \"$project\", type = \"$type\", source = \"$source\"");

        $indexHandler = new ProjectIndex($project);
        $importer = new Importer($ret['source'][$source]);

        $total = $indexHandler->import($importer, $type);

        return $this->response(200, "共索引 $total 筆資料\n");
    }

    /**
     * nextIdAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("顯示下一個做為索引的 id")
     *
     * @arg("PROJECT TYPE")
     */
    public function nextIdAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params['project'] ?? $params[0];
        $type    = $params['type'] ?? $params[1];

        $indexHandler = new ProjectIndex($project);
        $ret = $indexHandler->findIndexIdForImport($type);

        return $this->response(200, ['result' => $ret]);
    }

    /**
     * switchAliasAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("修改對應的 Alias id")
     *
     * @arg("PROJECT TYPE ID")
     */
    public function switchAliasAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params['project'] ?? $params[0];
        $type    = $params['type'] ?? $params[1];
        $tid     = $params['aliasId'] ?? $params[2];

        $indexHandler = new ProjectIndex($project);
        $ret = $indexHandler->updateAlias($tid, $type);
        if (isset($ret['result']['error'])) {
            return $this->response($ret['result']['status'], $ret);
        }

        if ($ret) {
            return $this->response(200, ['result' => $ret]);
        }

        return $this->response(400, '參數不符，設定失敗');
    }

    /**
     * indexDoc
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("新增/替換文件 (ex: indexDoc gohappy product 12345 /tmp/xxx.json)")
     *
     * @arg("PROJECT TYPE DOCID JSON/FILE")
     */
    public function indexDocAction($params)
    {
        if (!$this->validateParams($params, 4)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params[0];
        $type    = $params[1];
        $docId   = $params[2];

        $data = $this->fetchParam($params[3]);
        if (!$data) {
            return $this->response(400, "json 格式錯誤\n");
        }

        $indexHandler = new ProjectIndex($project);
        $result = $indexHandler->setType($type)->indexDoc($docId, $data);
        $status = 400;
        if (in_array($result['result'], ['created', 'updated'])) {
            $status = 200;
        }
        return $this->response($status, ['result' => $result]);
    }

    /**
     * updateDoc
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("更新文件 (ex: updateDoc gohappy product 12345 /tmp/xxx.json)")
     *
     * @arg("PROJECT TYPE DOCID JSON/FILE")
     */
    public function updateDocAction($params)
    {
        if (!$this->validateParams($params, 4)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params[0];
        $type    = $params[1];
        $docId   = $params[2];

        $data = $this->fetchParam($params[3]);
        if (!$data) {
            return $this->response(400, "json 格式錯誤\n");
        }

        $indexHandler = new ProjectIndex($project);
        $result = $indexHandler->setType($type)->updateDoc($docId, $data);
        $status = 400;
        if ($result['result'] == 'updated') {
            $status = 200;
        }
        return $this->response($status, ['result' => $result]);
    }

    /**
     * deleteDoc
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除文件")
     *
     * @arg("PROJECT TYPE DOCID")
     */
    public function deleteDocAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params[0];
        $type    = $params[1];
        $docId   = $params[2];
        $indexHandler = new ProjectIndex($project);
        $result = $indexHandler->setType($type)->deleteDoc($docId);

        if (isset($result['result'])) {
            // \GoSearch\Helper\Message::debugLog($result['result']);
            return $this->response(200, $result);
        }

        if (!$result) {
            return $this->response(404, "文件不存在\n");
        }

        return $this->response(200, "刪除成功\n");
    }
}
