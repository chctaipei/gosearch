<?php
namespace GoSearch\Task;

use GoSearch\Admin;
use GoSearch\ProjectSearch;
use GoSearch\QueryLog;
use GoSearch\Boost;
use GoSearch\HotQuery;
use GoSearch\SearchHelper;

/**
 * SearchTask
 *
 * @subject("搜尋")
 *
 * @author("hc_chien <hc_chien@hiiir.com>")
 */
class SearchTask extends MainTask
{

    /**
     * checkAlnum
     *
     * @param string $param param
     *
     * @return boolean
     */
    private function checkAlnum($param)
    {
        $check = $param;

        if (substr($param, -7) == ".schema") {
            $check = substr($param, 0, -7);
        }

        if (!ctype_alnum($check)) {
            return false;
        }
        return true;
    }

    /**
     * putScriptAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("設定搜尋樣板")
     *
     * @arg("PROJECT SCRIPTID SOURCE")
     */
    public function putScriptAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        if (!$this->checkAlnum($params[1])) {
            return $this->response(400, "使用英數字\n");
        }

        try {
            $projectSearch = new ProjectSearch($params[0]);
            // no JSON decode
            $data = $this->fetchParam($params[2], false);
            if ($data == "") {
                return $this->deleteScriptAction($params);
            }
            $result = $projectSearch->putScript($params[1], $data);
            $result = $projectSearch->getScript($params[1]);
            return $this->response(200, $result);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            return $this->response(400, "未完成初始化, 請先執行 initSystem\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * getScriptAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("取得搜尋樣板, PROJECT 可以用 * 表示全部")
     *
     * @arg("PROJECT [SCRIPTID]")
     */
    public function getScriptAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }
        $projectSearch = new ProjectSearch($params[0]);
        if (isset($params[1])) {
            $result = $projectSearch->getScript($params[1]);
            return $this->response(200, ['result' => $result]);
        }
        $result = $projectSearch->getAllScript();
        return $this->response(200, ['result' => $result]);
    }

    /**
     * deleteScriptAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除搜尋樣板")
     *
     * @arg("PROJECT SCRIPTID")
     */
    public function deleteScriptAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $projectSearch = new ProjectSearch($params[0]);
        $message = $projectSearch->deleteScript($params[1]);
        $code = $message['status'] ?? 200;

        // 額外刪除 schema
        if (!strstr($params[1], ".schema")) {
            $params[1] .= ".schema";
            $projectSearch->deleteScript($params[1]);
        }
        return $this->response($code, $message);
    }

    /**
     * renameScriptAction
     *
     * 更名時:
     * 1. 舊的 script & schema 的內容都要複製
     * 2. syncMatches 的對應 scriptname 也要換
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("樣板更名")
     *
     * @arg("PROJECT OLD_CRIPTID NEW_SCRIPTID")
     */
    public function renameScriptAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        if (!$this->checkAlnum($params[2])) {
            return $this->response(400, "使用英數字\n");
        }

        $projectSearch = new ProjectSearch($params[0]);
        $data = $projectSearch->getScript($params[1]);
        $result = $projectSearch->putScript($params[2], $data);

        if (!strstr($params[1], ".schema")) {
            $this->renameSchemaAction($params);
        }

        // 這個動作要最後做
        $projectSearch->deleteScript($params[1]);

        return $this->response(200, ['result' => $result]);
    }

    /**
     * putSchemaAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("設定樣板的 JSON/UI SCHEMA")
     *
     * @arg("PROJECT SCRIPTID SOURCE")
     */
    public function putSchemaAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        if (!strstr($params[1], ".schema")) {
            $params[1] .= ".schema";
        }
        return $this->putScriptAction($params);
    }

    /**
     * getSchemaAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("取得樣板的 JSON/UI SCHEMA")
     *
     * @arg("PROJECT [SCRIPTID]")
     */
    public function getSchemaAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $projectSearch = new ProjectSearch($params[0]);
        if (isset($params[1])) {
            if (!strstr($params[1], ".schema")) {
                $params[1] .= ".schema";
            }
            $data = $projectSearch->getScript($params[1]);
            if ($data && is_string($data)) {
                $data = json_decode($data, 1);
            }
            return $this->response(200, ['result' => $data]);
        }

        $result = $projectSearch->getAllSchema();
        foreach ($result as $name => $data) {
            if ($data && is_string($data)) {
                $result[$name] = json_decode($data, 1);
            }
        }
        return $this->response(200, ['result' => $result]);
    }

    /**
     * deleteSchemaAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除樣板的 JSON/UI SCHEMA")
     *
     * @arg("PROJECT SCRIPTID")
     */
    public function deleteSchemaAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        if (!strstr($params[1], ".schema")) {
            $params[1] .= ".schema";
        }
        return $this->deleteScriptAction($params);
    }

    /**
     * renameSchemaAction 給前端使用, cli 應該用 renameScript
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("樣板更名")
     *
     * @arg("PROJECT OLD_CRIPTID NEW_SCRIPTID")
     */
    public function renameSchemaAction($params)
    {
        if (!strstr($params[1], ".schema")) {
            $params[1] .= ".schema";
        }

        if (!strstr($params[2], ".schema")) {
            $params[2] .= ".schema";
        }

        return $this->renameScriptAction($params);
    }

    /**
     * 取文件
     *
     * @param array $params parameter
     *
     * @return void
     *
     * @subject("取文件")
     *
     * @arg("PROJECT TYPE DOCID")
     */
    public function getDocAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $service = new ProjectSearch($params[0]);
        $service->setType($params[1]);
        $ret = $service->getDoc($params[2]);
        return $this->response($ret['status'], $ret);
    }

    /**
     * searchAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("利用樣板(script)做搜尋")
     *
     * @arg("PROJECT TYPE SCRIPTNAME JSON")
     */
    public function searchTemplateAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project    = $params[0];
        $type       = $params[1];
        $scriptName = $params[2];

        // 避免將 {} => [] 造成 params doesn't support values of type: START_ARRAY 的錯誤
        $body = (object) $this->fetchParam($params[3]);

        // 模糊同音搜尋 (需將關鍵字轉成同音字)
        if ($type == 'hotquery' && $scriptName == 'fuzzy' && isset($body->query)) {
            $keyword = $body->query;
            $body->query = SearchHelper::getPhonetic(SearchHelper::filterQuery($keyword));
        }

        $service = new ProjectSearch($project);
        // $service->setType($params[1]);
        $ret = $service->searchTemplate($scriptName, $body, $type);
        $status = $ret['status'] ?? 200;
        return $this->response($status, $ret);
    }

    /**
     * 搜尋
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("搜尋 (可以用 json 檔案)")
     *
     * @arg("PROJECT TYPE JSON")
     */
    public function searchAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $body = $this->fetchParam($params[2]);
        $service = new ProjectSearch($params[0]);
        $service->setType($params[1]);

        // 模糊同音搜尋 (需將關鍵字轉成同音字)
        if ($params[1] == 'hotquery' && isset($body['query'])) {
            $keyword = $body['query'];
            $body['query'] = SearchHelper::getPhonetic(SearchHelper::filterQuery($keyword));
        }

        $ret = $service->search($body);
        $status = $ret['status'] ?? 200;
        return $this->response($status, $ret);
    }

    /**
     * 搜尋 (OLD)
     *
     * @param array $params parameter
     *
     * @return array
     *
     * subject("搜尋 (SCHEMA 與 TERM 可以用 json 檔案)")
     *
     * arg("PROJECT SCHEMA [TERM]")
     */
    public function queryAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $schema = $this->fetchParam($params[1]);
        if (!$schema) {
            $schema = $params[1];
        }

        if (is_array($schema) && !isset($schema['index'])) {
            $type = $schema['type'];
            $schema['index'] = "{$params[0]}_{$type}";
        }

        $qarr = [];
        if (isset($params[2])) {
            $qarr = $this->fetchParam($params[2]);
            if (!$qarr) {
                $qarr = ['keyword' => $params[2]];
            }
        }

        $service = new ProjectSearch($params[0]);
        $ret = $service->query($qarr, $schema);
        return $this->response(200, $ret);
    }

    /**
     * 列出熱門關鍵字
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("列出熱門關鍵字 (數量,欄位,建立時間大於幾天以上)")
     *
     * @arg("PROJECT [COUNT=10] [COLUMN='query,matches'] [DAYS=0]")
     */
    public function listHotAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }
        $project = $params[0];
        $count   = $params[1] ?? 10;
        $column  = $params[2] ?? 'query,matches';
        $days    = $params[3] ?? 0;

        $queryLog = new QueryLog($project);
        $ret      = $queryLog->getHotWords($count, $column, $days);
        return $this->response(200, ['hotwords' => $ret]);
    }

    /**
     * 更新熱門關鍵字
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("更新熱門關鍵字分數")
     *
     * @arg("PROJECT KEYWORD COUNT")
     */
    public function updateHotAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }
        $project = $params[0];
        $query   = $params[1];
        $count   = $params[2];

        $queryLog = new QueryLog($project);
        $ret      = $queryLog->updateCount($query, $count);
        if ($ret) {
            return $this->response(200, '更新成功');
        }
        return $this->response(400, '內容相同或不存在');
    }

    /**
     * 測試 auto-complete
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("測試 autocomplete, ex: a")
     *
     * @arg("PROJECT TERM")
     */
    public function suggestAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $queryLog = new QueryLog($params[0]);
        $ret      = $queryLog->getSuggestion($params[1]);
        return $this->response(200, ['suggestion' => $ret]);
    }

    /**
     * 搜尋相關關鍵字
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("搜尋相關關鍵字")
     *
     * @arg("PROJECT KEYWORD")
     */
    public function fuzzyAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        // 模糊同音搜尋 (需將關鍵字轉成同音字)
        $body['query'] = SearchHelper::getPhonetic(SearchHelper::filterQuery($params[1]));

        $service = new ProjectSearch($params[0]);
        $ret = $service->searchTemplate('fuzzy', $body, 'hotquery');

        if (isset($ret['error'])) {
            return response(200, ['fuzzy' => []]);
        }

        $arr = [];
        foreach ($ret['hits']['hits'] as $hits) {
            $arr[] = $hits['_source']['name'];
        }
        return $this->response(200, ['fuzzy' => $arr]);
    }


    /**
     * 紀錄關鍵字
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("紀錄關鍵字 (querylog)")
     *
     * @arg("PROJECT KEYWORD MATCHES")
     */
    public function logAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $queryLog = new QueryLog($params[0]);
        $ret = $queryLog->insertQuery($params[1], $params[2]);
        if ($ret) {
            return $this->response(200, "更新");
        }
        return $this->response(400, "失敗");
    }

    /**
     * 紀錄點擊
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("紀錄點擊 (boost)")
     *
     * @arg("PROJECT DOCID KEYWORD")
     */
    public function clickAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤\n");
        }

        $boost = new Boost($params[0]);
        // $boost->test();
        // exit;
        $ret = $boost->insertRecord($params[1], $params[2]);
        if ($ret) {
            return $this->response(200, "更新");
        }
        return $this->response(400, "失敗");
    }
}
