<?php
namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;
use GoSearch\Constant;
use GoSearch\Admin;
use GoSearch\Importer;

/**
 * 專案索引
 *
 * @author hc_chien <hc_chien>
 */
class ProjectIndex extends Index
{
    const ALL_INDEX_TYPE = '*';
    const CHUNKSIZE = 1000;
    protected $project;
    protected $config;

    /**
     * __construct
     *
     * @param string $project 專案
     *
     * @return $this
     */
    public function __construct($project = null)
    {
        if ($project) {
            $this->project = $project;
        }

        $projectObj = new Project();
        $ret = $projectObj->getProjects();
        foreach ($ret['hits'] as $hits) {
            $this->config[$hits['name']] = $hits['data'];
        }

        return $this;
    }

    /**
     * setType
     *
     * @param string $type type
     *
     * @return $this
     */
    public function setType($type)
    {
        if (!$this->project || !is_string($type)) {
            throw new \Exception("type 為空或非字串");
        }

        parent::setIndex("{$this->project}_{$type}");
        parent::setType($type);
        return $this;
    }

    /**
     * setArg
     *
     * @param string $project project
     * @param string $type    type
     *
     * @return array
     */
    private function setArg($project, $type)
    {
        if (!$project) {
            $project = $this->project;
        }

        if (!$type) {
            $type = $this->type;
        }

        if (!$type || !$project) {
            throw new \Exception("no project or type");
        }

        return [$project, $type];
    }

    /**
     * walk
     *
     * @param mixed $project  project
     * @param mixed $type     type
     * @param mixed $callback callback
     *
     * @return void
     */
    private function walk($project, $type, $callback)
    {
        foreach ($this->config as $project2 => $config) {
            if (!isset($config['index'])) {
                continue;
            }

            if ($project2 == $project || $project == self::ALL_INDEX_TYPE) {
                foreach ($config['index'] as $type2 => $schema) {
                    if ($type == $type2 || $type == self::ALL_INDEX_TYPE) {
                        $index = "{$project2}_{$type2}";
                        $callback($project2, $type2, $index, $schema, $config);
                    }
                }
            }//end if
        }//end foreach
    }

    /**
     * createIndex
     *
     * @param string $index  index
     * @param string $type   type
     * @param array  $schema schema
     *
     * @return array
     */
    private function createIndex($index, $type, $schema)
    {
        $searchClient = $this->getClient();
        $params = [
            'index'  => $index,
            'body'   => $schema,
            'client' => [ 'ignore' => [400, 404] ]
        ];
        $ret = $searchClient->indices()->create($params);
        if (isset($ret['error']['type']) && $ret['error']['type'] == "index_already_exists_exception") {
            $params['type'] = $type;
            $params['body'] = $schema['mappings'];
            $ret = $searchClient->addTypeMapping($params);
        }
        return $ret;
    }

    /**
     * 建立索引 (index & type)
     *
     * @param string $type    index type
     * @param string $project project
     * @param int    $tid     type id
     *
     * @return ['status' => int , 'message' => array ]
     */
    public function create($type = null, $project = null, $tid = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $result['status'] = 200;
        $this->walk(
            $project,
            $type,
            function ($project2, $type2, $index, $schema, $config) use (&$result, $tid) {
                $backups = $config['backup'][$type2]['count'] ?? 0;
                if ($backups > 0) {
                    for ($i = 1; $i <= $backups + 1; $i++) {
                        if ($tid === null || $i == $tid) {
                            $result['message']["{$index}_{$i}"] = $this->createIndex($index . "_$i", $type2, $schema);
                        }
                    }

                    if ($tid == null) {
                        // 設定對應
                        // $searchClient = $this->getClient();
                        // $searchClient->setAlias("{$index}_1", $index);
                        $this->updateAlias(1, $type2, $project2);
                    }
                } else {
                    $result['message'][$index] = $this->createIndex($index, $type2, $schema);
                }

                 \GoSearch\Helper\Message::debugLog("[ProjectIndex] create index: {$index}");
                if (isset($result['message'][$index]['mapping']['error'])) {
                    $result['status'] = $result['message'][$index]['mapping']['status'];
                }
                if (isset($result['message'][$index]['error'])) {
                    $result['status'] = $result['message'][$index]['status'];
                }
            }
        );
        return $result;
    }

    /**
     * 變更 alias 對應
     *
     * @param int    $tid     tid
     * @param string $type    type
     * @param string $project project
     *
     * @return array
     */
    public function updateAlias($tid, $type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $backups = $this->config[$project]['backup'][$type]['count'] ?? 0;

        // illegal $tid
        if ($backups < 1 || $tid <= 0 || $tid > $backups + 1) {
            return false;
        }

        $index = "{$project}_{$type}";
        $searchClient = $this->getClient();
        return $searchClient->setAlias("{$index}_{$tid}", $index);
    }

    /**
     * 取資料庫存放的 mapping 設定
     *
     * @param string $type    type
     * @param string $project project
     *
     * @return ['status' => int , 'result' => array ]
     */
    public function get($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);
        $result['status'] = 200;
        $result['result'] = [];

        $this->walk(
            $project,
            $type,
            function ($project2, $type2, $index, $schema, $config) use (&$result) {
                unset($schema);

                // a. has backup
                $backups = $config['backup'][$type2]['count'] ?? 0;
                if ($backups) {
                    $result['result'][$index]['project'] = $project2;
                    $result['result'][$index]['type']    = $type2;
                    $result['result'][$index]['count'] = 0;
                    $ret = $this->getIndex($type2 . "_*", $project2);
                    ksort($ret);
                    $result['result'][$index]['backups'] = $ret;
                    $result['result'][$index]['status'] = $ret ? 200 : 404;
                    foreach ($ret as $key => $value) {
                        list($project2, $type2, $tid) = explode("_", $key, 3);
                        $count = $this->getDocCount($type2, $project2, $tid);
                        $result['result'][$index]['backups'][$key]['count'] = $count;
                        if (isset($value['aliases'][$index])) {
                            // 實際名字 = ['settings'][['index']['provided_name']
                            $result['result'][$index]['alias'] = $key;
                            $result['result'][$index]['mappings'] = $value['mappings'];
                            $result['result'][$index]['settings'] = $value['settings'];
                            $result['result'][$index]['count'] = $count;
                        }
                        unset($result['result'][$index]['backups'][$key]['mappings']);
                        unset($result['result'][$index]['backups'][$key]['aliases']);
                    }

                    return;
                }//end if

                // b. no backup
                $ret = $this->getIndex($type2, $project2);
                if (isset($ret['error'])) {
                    $result['result'][$index] = $ret;
                    $result['result'][$index]['count'] = 0;
                    $result['result'][$index]['status'] = 404;
                } else {
                    $result['result'][$index] = current($ret);
                    $result['result'][$index]['count'] = $this->getDocCount($type2, $project2);
                    $result['result'][$index]['status'] = 200;
                }
                $result['result'][$index]['project'] = $project2;
                $result['result'][$index]['type']    = $type2;
            }
        );
        return $result;
    }

    /**
     * 取 ElasticSearch 實際建立的 mapping
     *
     * @param string $type    type
     * @param string $project project
     *
     * @return array
     */
    public function getCreatedMapping($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);
        $projectNames = array_keys($this->config);
        $params = [
            'index'  => "{$project}_{$type}",
            'client' => [ 'ignore' => [400, 404] ]
        ];

        $result = $this->getClient()->getTypeMapping($params);
        if ($project != "*") {
            return $result;
        }

        $return = [];
        foreach ($result as $name => $data) {
            list($project2, $type2) = explode("_", $name, 2);
            unset($type2);
            if (in_array($project2, $projectNames)) {
                $return[$project2][$name] = $data;
            }
        }
        return $return;
    }


    /**
     * getIndex
     *
     * @param string $type    type
     * @param string $project project
     *
     * @return array
     */
    public function getIndex($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $params = [
            'index'  => "{$project}_{$type}",
            'client' => [ 'ignore' => [400, 404] ]
        ];
        return $this->getClient()->getIndex($params);
    }

    /**
     * getMapping
     *
     * @param string $type    type
     * @param string $project project
     *
     * @return array
     */
    public function getMapping($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $params = [
            'index'  => "{$project}_{$type}",
            'type'   => $type,
            'client' => [ 'ignore' => [400, 404] ]
        ];
        return $this->getClient()->getTypeMapping($params);
    }

    /**
     * get document count
     *
     * @param string $type    type
     * @param string $project project
     * @param int    $tid     alias id
     *
     * @return int
     */
    public function getDocCount($type = null, $project = null, $tid = 0)
    {
        [$project, $type] = $this->setArg($project, $type);
        $index = "{$project}_{$type}";
        if ($tid) {
            $index .= "_$tid";
        }

        $params = [
            'index'  => $index,
            'type'   => $type,
            'client' => [ 'ignore' => [400, 404] ],
            'body'   => ['size' => 0]
        ];

        $ret = $this->getClient()->doSearch([], $params);
        if (isset($ret['hits']['total'])) {
            return $ret['hits']['total'];
        }
        return 0;
    }

    /**
     * 比較兩個 schema 是否一樣
     *
     * @param array $array1 schema1
     * @param array $array2 schema2
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function isMatchSchema($array1, $array2)
    {
        $ret = true;

        $cmpFunc = function (&$item, $key, $misc) use (&$ret) {
            $target  = $misc[0];
            $cmpFunc = $misc[1];

            if (!isset($target[$key]) && $key != 'analyzer') {
                if ($key == 'index' && ($item == 'analyzed' || $item == "not_analyzed")) {
                    return;
                }
                // \GoSearch\Helper\Message::debugLog("[isMatchSchema] key[$key] is not exist\n");
                $ret = false;
                return;
            }

            if (is_array($item)) {
                return array_walk($item, $cmpFunc, [$target[$key], $cmpFunc]);
            } else {
                if ($target[$key] != $item) {
                    if ($key == 'index') {
                        if (in_array($target[$key], ['no', false])) {
                            return;
                        }
                    }
                    // \GoSearch\Helper\Message::debugLog("[isMatchSchema] key[$key] is not matched\n");
                    $ret = false;
                    return;
                }
            }
        };

        array_walk($array1, $cmpFunc, [$array2, $cmpFunc]);

        // 如果要精準, 前後比較兩次
        // array_walk($array2, $cmpFunc, [$array1, $cmpFunc]);
        return $ret;
    }

    /**
     * checkMapping
     *
     * @param string $type    type
     * @param string $project project
     *
     * @return ['status' => int , 'message' => string ]
     */
    public function checkMapping($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        if (!isset($this->config[$project]['index'][$type])) {
            return ['status' => 40401, 'message' => "設定檔不存在"];
        }

        $from = $this->config[$project]['index'][$type];

        $ret = $this->getMapping($type);
        if (isset($ret['status']) && $ret['status'] == 404) {
            return ['status' => 40402, 'message' => "索引尚未完成建立"];
        }

        // 修正 alias 的影響
        $arr = current($ret);
        $existed = $arr['mappings'];

        if ($this->isMatchSchema($from, $existed)) {
            $count = $this->getDocCount($type);
            return ['status' => 200, "message" => "文件數量: $count" ];
        }

        return ['status' => 409, 'message' => "索引與設定不一致, 需刪除重建"];
    }

    /**
     * getAlias
     *
     * @param mixed $alias alias
     *
     * @return array
     */
    public function getAlias($alias)
    {
        $searchClient = $this->getClient();
        $params = [
            'name'   => $alias,
            'client' => [ 'ignore' => [400, 404] ],
        ];
        return $searchClient->indices()->getAlias($params);
    }

    /**
     * getAliasId
     *
     * @param string $type    index type
     * @param string $project project
     *
     * @return aliasId
     */
    public function getAliasId($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);
        $alias = "{$project}_{$type}";

        $ret = $this->getAlias($alias);
        if (isset($ret['status']) && $ret['status'] == 404) {
            return 0;
        }

        sscanf(key($ret), "{$alias}_%d", $tid);
        if ($tid == null) {
            return 0;
        }

        return $tid;
    }

    /**
     * 刪除索引 (index & type)
     *
     * @param string $type    index type
     * @param string $project project
     * @param int    $tid     type id
     *
     * @return ['status' => int , 'message' => array ]
     */
    public function delete($type = null, $project = null, $tid = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $result['status'] = 200;
        $this->walk(
            $project,
            $type,
            function ($project2, $type2, $index, $schema, $config) use (&$result, $tid) {
                unset($project2, $schema);
                $backups = $config['backup'][$type2]['count'] ?? 0;
                $searchClient = $this->getClient();
                try {
                    if ($backups > 0) {
                        $searchClient->deleteAlias($index);
                        for ($i = 1; $i <= $backups + 1; $i++) {
                            if ($tid === null || $i == $tid) {
                                $result['message']["{$index}_{$i}"] = $searchClient->deleteIndex($index . "_$i");
                            }
                        }
                    } else {
                        $result['message'][$index] = $searchClient->deleteIndex($index);
                    }
                } catch (\Exception $e) {
                    $result['status'] = 500;
                    $result['message'] = $e->getMessage();
                    return $result;
                }
            }
        );

        return $result;
    }

    /**
     * bulkAction
     *
     * @param string $action  action
     * @param array  $feed    feed
     * @param string $type    type
     * @param string $project project
     * @param string $index   index (真實的 index)
     * @param string $docName mapping 到 docid, feed 如果已有設定 docid, 則不需要傳遞這個欄位
     *
     * @return array
     */
    public function bulkAction($action, $feed, $type = null, $project = null, $index = null, $docName = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $param = [
            'index'  => $index ? $index : "{$project}_{$type}",
            'type'   => $type,
            'client' => [ 'ignore' => [400, 404] ]
        ];

        // \GoSearch\Helper\Message::debugLog($param);
        // 用 yield 從來源端控制或在這裡用 chunk, 兩種方式都可以
        // 參考: task/IndexTask::importAction()
        $feedChunk = array_chunk($feed, self::CHUNKSIZE);
        $ret = [];
        foreach ($feedChunk as $chunk) {
            $result = parent::bulk($chunk, $param, $action, $docName);
            if (isset($result['errors']) && $result['errors'] > 0) {
                \GoSearch\Helper\Message::debugLog("[bulk] Error");
                foreach ($result['items'] as $item) {
                    if (isset($item["index"]['error'])) {
                        \GoSearch\Helper\Message::debugLog($item);
                    }
                }
            }
            $ret[] = $result;
        }

        return $ret;
    }

    /**
     * 批次部分內容更新
     *
     * @param array  $feed    feed
     * @param string $type    type or param
     * @param string $project project
     * @param string $index   index
     * @param string $docName mapping 到 docid, feed 如果已有設定 docid, 則不需要傳遞這個欄位
     *
     * @return array
     */
    public function bulkUpdate($feed, $type = null, $project = null, $index = null, $docName = null)
    {
        return $this->bulkAction(SearchClient::BULK_UPDATE, $feed, $type, $project, $index, $docName);
    }

    /**
     * 批次新增或取代
     *
     * @param array  $feed    feed
     * @param string $type    type or param
     * @param string $project project
     * @param string $index   index
     * @param string $docName mapping 到 docid, feed 如果已有設定 docid, 則不需要傳遞這個欄位
     *
     * @return array
     */
    public function bulkIndex($feed, $type = null, $project = null, $index = null, $docName = null)
    {
        return $this->bulkAction(SearchClient::BULK_INDEX, $feed, $type, $project, $index, $docName);
    }

    /**
     * findIndexIdForImport
     *
     * @param string $type    type
     * @param string $project project
     *
     * @return integer
     */
    public function findIndexIdForImport($type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $backups = $this->config[$project]['backup'][$type]['count'] ?? 0;
        $tid = $this->getAliasId($type, $project);

        if ($tid == 0) {
            // 有設定 backup 但 index 未建立 alias
            if ($backups) {
                return 1;
            }
            return 0;
        }

        $backups = $this->config[$project]['backup'][$type]['count'] ?? 0;
        $min = 1;
        $arr = $this->config[$project]['backup'][$type];
        for ($i = 1; $i < $backups + 1; $i++) {
            if ($i == $tid) {
                continue;
            }

            if ($arr[$i] == null) {
                return $i;
            }

            if ($arr[$i] < $arr[$min]) {
                $min = $i;
            }
        }
        return $min;
    }


    /**
     * import
     *
     * @param Importer $importer importer
     * @param string   $type     type
     * @param string   $project  project
     *
     * @return integer
     */
    public function import(Importer $importer, $type = null, $project = null)
    {
        [$project, $type] = $this->setArg($project, $type);

        $index = "{$project}_{$type}";
        $tid   = $this->findIndexIdForImport($type, $project);
        if ($tid) {
            $index .= "_$tid";

            // 刪掉
            $ret = $this->delete($type, $project, $tid);
            // \GoSearch\Helper\Message::debugLog($ret);
            // 重建
            $ret = $this->create($type, $project, $tid);
            // \GoSearch\Helper\Message::debugLog($ret);
        }
        \GoSearch\Helper\Message::debugLog("[import] index = {$index}");

        $total = 0;
        $errors = 0;
        foreach ($importer->read() as $arr) {
            $ret = $this->bulkIndex($arr, $type, $project, $index);
            foreach ($ret as $result) {
                if (isset($result['errors']) && $result['errors'] > 0) {
                    foreach ($result['items'] as $item) {
                        if (isset($item["index"]['error'])) {
                            $errors++;
                        }
                    }
                }
            }
            $total += count($arr);
            \GoSearch\Helper\Message::debugLog("[import] 已執行 $total 筆資料, $errors 筆錯誤");
        }

        if ($tid && $total) {
            $this->updateAlias($tid, $type, $project);
            $projectObj = new Project($project);
            $projectObj->updateBackupTime($type, $tid);
        }

        return $total - $errors;
    }

    /**
     * setTemplate
     *
     * @param string $name name
     * @param string $code code
     *
     * @return array
     */
    public function putTemplate($name, $code)
    {
        $name = "{$this->project}_{$name}";
        if (is_string($code)) {
            $code = json_decode($code, 1);
        }

        // ES 6.0 以上, 改名為 "index_patterns"
        if (!isset($code['template'])) {
            $code['template'] = $name;
        }

        $params = [
            'name' => $name,
            'body' => $code
        ];
        $searchClient = $this->getClient();
        return $searchClient->indices()->putTemplate($params);
    }

    /**
     * getTemplate
     *
     * @param mixed $name name
     *
     * @return array
     */
    public function getTemplate($name)
    {
        $name = "{$this->project}_{$name}";
        $params = [
            'name'   => $name,
            'client' => [ 'ignore' => [400, 404] ],
        ];
        $searchClient = $this->getClient();
        return $searchClient->indices()->getTemplate($params);
    }

    /**
     * deleteTemplate
     *
     * @param mixed $name name
     *
     * @return array
     */
    public function deleteTemplate($name)
    {
        $name = "{$this->project}_{$name}";
        $params = [
            'name'   => $name,
            'client' => [ 'ignore' => [400, 404] ],
        ];
        $searchClient = $this->getClient();
        return $searchClient->indices()->deleteTemplate($params);
    }

    /**
     * getAllTemplate
     *
     * @return array
     */
    public function getAllTemplate()
    {
        return $this->getTemplate("*");
    }
}
