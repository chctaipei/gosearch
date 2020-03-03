<?php

namespace GoSearch;

use Elasticsearch\ClientBuilder;
use GoSearch\SearchHelper;
use GoSearch\Exception\SearchException;

/**
 * ElasticSearch Provider
 *
 * @author hc_chien <hc_chien>
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SearchClient
{
    const BULK_CREATE = "create";
    const BULK_INDEX  = "index";
    const BULK_UPDATE = "update";
    const BULK_DELETE = "delete";

    private static $esClient = null;
    private static $config   = null;
    private static $mapping;

    /**
     * 建構子
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (self::$esClient) {
            return;
        }

        // self::$mapping = yaml_parse_file(__DIR__."/../config/mapping.yml");
        // 未來要移除
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        if (!$di || !$di->has("config")) {
            return;
        }

        $config = $di->get("config");
        self::$config = $config->gosearch->elasticSearch ?? $config->elasticSearch;
    }

    /**
     * Initialize Search Client
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    private function initClient()
    {
        if (self::$esClient) {
            return;
        }

        if (isset(self::$config->host) && isset(self::$config->port)) {
            $hosts          = [self::$config->host.':'.self::$config->port];
            self::$esClient = ClientBuilder::create()->setHosts($hosts)->build();
        }
    }

    /**
     * replaceTag
     *
     * @param mixed $body     body
     * @param mixed $tag      tag
     * @param mixed $replaced replaced
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function replaceTag(&$body, $tag, $replaced)
    {
        array_walk_recursive(
            $body,
            function (&$val, $key, $replaced) use ($tag) {
                if ($val === $tag) {
                    $val = $replaced;
                }
            },
            $replaced
        );
    }

    /**
     * 分析 parameter
     *
     * @param array $qarr   query array
     * @param array $params params
     *
     * @return array $body
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function convertQuery($qarr, $params)
    {
        $body = $params['body'];
        $keyword = '';
        $filter  = [];

        foreach (array_keys($qarr) as $name) {
            switch ($name) {
                case 'limit':
                    $body['size'] = $qarr['limit'];
                    break;
                case 'page':
                    if (isset($qarr['limit'])) {
                        $body['from'] = (($qarr['page'] - 1) * $qarr['limit']);
                    }
                    break;
                case 'keyword':
                    $keyword = trim(str_replace('"', ' ', $qarr['keyword']));
                    break;
                case 'aggs':
                    if (!$qarr['aggs']) {
                        unset($body['aggs']);
                    } elseif (is_string($qarr['aggs'])) {
                        $tmp[$qarr['aggs']] = $body['aggs'][$qarr['aggs']];
                        $body['aggs']        = $tmp;
                    }
                    break;
                case 'list':
                    if ($qarr['list']) {
                        unset($body['query']);
                    }
                    break;
                case 'sort':
                    $body["sort"] = $qarr['sort'];
                    break;
                case 'filter':
                    $filter = $qarr['filter'];
                    break;
            }//end switch
        }//end foreach

        $this->replaceTag($body, ':QUERY:',  $keyword);
        $this->replaceTag($body, ':FILTER:', $filter);

        $params['body'] = $body;

        // 只回傳部分欄位
        if (isset($qarr['_source']) && $qarr['_source']) {
            $params['_source'] = $qarr['_source'];
            unset($params['body']['_source']);
        }

        unset($qarr['counting']);

        return $params;
    }

    /**
     * 搜尋
     *
     * @param array $qarr   query array
     * @param array $params ['index' => $index, 'type' => $type, 'body' => $body]
     *
     * @return array 搜尋結果
     */
    public function doSearch($qarr, $params)
    {
        $params = $this->convertQuery($qarr, $params);
        $this->initClient();
        return self::$esClient->search($params);
    }

    /**
     * search
     *
     * @param array $params ['index' => $index, 'type' => $type, 'body' => $body]
     *
     * @return array
     */
    public function search($params)
    {
        $this->initClient();
        return self::$esClient->search($params);
    }

    /**
     * 刪除資料 (搜尋)
     * $params = [
     * 'id'    => $docId, (文件 docId, 非必要)
     * 'index' => $index,
     * 'type'  => $type,
     * ];
     *
     * @param array $params ['id' => $id, 'index' => $index, 'type' => $type]
     *
     * @return array 結果
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function remove($params)
    {
        // 刪除資料
        try {
            $this->initClient();
            return self::$esClient->delete($params);
        } catch (\Exception $e) {
            // product not found
            if (404 == $e->getCode()) {
                return false;
            }

            \GoSearch\Helper\Message::exceptionLog($e);
            $debugMessage = json_encode($params, JSON_UNESCAPED_UNICODE);
            throw new SearchException($debugMessage, 100001, $e);
        }
    }

    /**
     * 大量更新(新增/刪除)資料 (搜尋)
     * (注意: $data 要額外加入 docid 作為 search _id)
     *
     * @param array  $data   data   body
     * @param array  $param  param ['index'=>$index,'type'=>$type]
     * @param string $action bulk   action: index, delete, create, delete, update
     *
     * @return array
     */
    public function bulk($data, $param, $action = self::BULK_INDEX)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $body) {
            // action
            if (isset($body['bulk_action'])) {
                $action = $body['bulk_action'];
            }

            $feed['body'][] = [
                $action => [
                    '_index' => $param['index'],
                    '_type'  => $param['type'],
                    '_id'    => $body['docid'],
                ]
            ];

            unset($body['docid']);
            unset($body['bulk_action']);

            switch ($action) {
                case 'index':
                case 'create':
                    $feed['body'][] = $body;
                    break;
                case 'update':
                    $feed['body'][]['doc'] = $body;
                    break;
                // 'delete' 不用 body
            }
        }//end foreach

        $this->initClient();

        return self::$esClient->bulk($feed);
    }

    /**
     * 建立一個 index
     *
     * @param string $index    index
     * @param array  $settings settings
     * @param array  $mappings mappings
     * @param array  $aliases  aliases
     *
     * @return array 結果
     */
    public function createIndex($index, $settings = null, $mappings = null, $aliases = null)
    {
        $params = [
            'index' => $index
        ];

        if (!empty($settings)) {
            $params['body']['settings'] = $settings;
        }

        if (!empty($mappings)) {
            $params['body']['settings'] = $mappings;
        }

        if (!empty($aliases)) {
            $params['body']['aliases'] = $aliases;
        }

        try {
            $this->initClient();
            \GoSearch\Helper\Message::debugLog("[createIndex] index = $index");
            return self::$esClient->indices()->create($params);
        } catch (\Exception $e) {
            // already exist
            if (400 == $e->getCode()) {
                return "existed";
            }

            $debugMessage = json_encode($params, JSON_UNESCAPED_UNICODE);
            \GoSearch\Helper\Message::exceptionLog($e);
            throw new SearchException($debugMessage, 100002, $e);
        }
    }

    /**
     * 移除掉一個 index
     * 請注意! 這是針對 search index 刪除, 請小心!!
     *
     * @param string $index index
     *
     * @return array 結果
     */
    public function deleteIndex($index)
    {
        $params = [
            'index'  => $index,
            'client' => [ 'ignore' => [404] ]
        ];

        try {
            $this->initClient();
            \GoSearch\Helper\Message::debugLog("[deleteIndex] index = $index");
            return self::$esClient->indices()->delete($params);
        } catch (\Exception $e) {
            \GoSearch\Helper\Message::exceptionLog($e);
            $debugMessage = json_encode($params, JSON_UNESCAPED_UNICODE);
            throw new SearchException($debugMessage, 100003, $e);
        }
    }

    /**
     * 取得 index type mapping
     *
     * @param array $params ['index'=>$index]
     *
     * @return array 結果
     */
    public function getIndex($params)
    {
        $this->initClient();
        return self::$esClient->indices()->get($params);
    }

    /**
     * 增加一個 index type mapping
     *
     * @param array $params ['index'=>$index,'type'=>$type,'body'=>$schema]
     *
     * @return array 結果
     */
    public function addTypeMapping($params)
    {
        $this->initClient();
        return self::$esClient->indices()->putMapping($params);
    }

    /**
     * 取得 index type mapping
     *
     * @param array $params ['index'=>$index,'type'=>$type]
     *
     * @return array 結果
     */
    public function getTypeMapping($params)
    {
        $this->initClient();
        return self::$esClient->indices()->getMapping($params);
    }

    /**
     * getMetadata
     *
     * @return array
     *
     * [GET] _cluster/state/metadata/
     */
    public function getMetadata()
    {
        $this->initClient();
        return self::$esClient->cluster()->state(['metric' => 'metadata']);
    }

    /**
     * 指定 name 對應到 index
     *
     * @param string $index index (ex: gohappy_product_1)
     * @param string $alias alias (ex: gohappy_product)
     *
     * @return array 結果
     */
    public function setAlias($index, $alias)
    {
        $this->initClient();

        $params = [
            'body'   => [
                "actions" => [
                              ["remove" => [ "index" => "*", "alias" => $alias ]],
                              ["add" => [ "index" => $index, "alias" => $alias ]]
                ]
            ],
            'client' => [ 'ignore' => [400, 404] ]
        ];

        \GoSearch\Helper\Message::debugLog("[setAlias] index = $index, alias = $alias");
        return self::$esClient->indices()->updateAliases($params);
    }

    /**
     * 刪除 alias
     *
     * @param string $alias alias (ex: gohappy_product)
     *
     * @return array 結果
     */
    public function deleteAlias($alias)
    {
        $this->initClient();

        $params = [
            'index'  => "*",
            'name'   => $alias,
            'client' => [ 'ignore' => [400, 404] ]
        ];

        \GoSearch\Helper\Message::debugLog("[deleteAlias] alias = $alias");
        return self::$esClient->indices()->deleteAlias($params);
    }

    /**
     * __call
     * public function searchTemplate($params)
     * public function get($params)
     * public function index($params)
     * public function update($params)
     * public function putScript($params)
     * public function getScript($params)
     * public function deleteScript($params)
     *
     * @param string $name      name
     * @param array  $arguments arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->initClient();
        return call_user_func_array([self::$esClient, $name], $arguments);
    }
}
