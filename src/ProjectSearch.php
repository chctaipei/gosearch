<?php

namespace GoSearch;

use GoSearch\QueryLog;
use GoSearch\Admin;

/**
 * Search Service
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProjectSearch extends SearchBase
{
    const LANG = "mustache";
    protected $project;
    protected $config;
    private $logger;

    /**
     * __construct
     *
     * @param string $project 專案
     *
     * @return void
     */
    public function __construct($project = null)
    {
        if ($project) {
            $this->project = $project;
        }
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
     * 設定 Query Logger
     *
     * @param QueryLog        $logger   Query Logger
     * @param \Phalcon\Config $dbConfig db config object
     *
     * @return void
     */
    public function setLogger($logger, $dbConfig = null)
    {
        $this->logger = $logger;
        if ($dbConfig) {
            $this->logger->initDbConnection($dbConfig);
        }
    }

    /**
     * 取得 Query Logger
     *
     * @return void
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            // @codeCoverageIgnoreStart
            $this->logger = new QueryLog($this->project);
        }

        // @codeCoverageIgnoreEnd
        return $this->logger;
    }

    /**
     * 蒐集 query logs (未來應改成非同步)
     * counting = auto 時
     * 1. sort 與 filter 都必須是空值
     * 2. page 是空值或  = 1
     * counting = force 時, 強制 log
     *
     * 範例:
     * {
     * "counting": "auto",
     * "keyword": "hello"
     * "sort": [...]
     * "filter": [...]
     * }
     *
     * @param array   $qarr    query array
     * @param integer $matches matches
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function logResult($qarr, $matches)
    {
        if ($matches <= 0) {
            return;
        }

        if ($qarr['counting'] == 'auto') {
            if (isset($qarr['filter']) && count($qarr['filter'])) {
                return;
            }
            if (isset($qarr['sort']) && count($qarr['sort'])) {
                return;
            }
            if (isset($qarr['page']) && $qarr['page'] > 1) {
                return;
            }
        } elseif ($qarr['counting'] != 'force') {
            return;
        }

        try {
            $queryLog = $this->getLogger();
            $queryLog->insertQuery($qarr['keyword'], $matches);
        } catch (\Exception $e) {
            // ignore error
            \GoSearch\Helper\Message::exceptionLog($e);
        }
    }

    /**
     * 搜尋
     *
     * @param array  $qarr   query array
     * @param string $schema schema
     *
     * @return array
     */
    public function query($qarr, $schema)
    {
        if (!$schema) {
            throw new \Exception("no query schema");
        }

        if (!is_array($schema)) {
            throw new \Exception("$schema should be an array");
        }
        $params = $schema;

        $searchClient = $this->getClient();
        $response = $searchClient->doSearch($qarr, $params);

        if (isset($qarr['keyword']) && isset($qarr['counting']) && $qarr['counting']) {
            $this->logResult($qarr, $response["hits"]["total"]);
        }

        return $response;
    }

    /**
     * search
     *
     * @param mixed $body body
     * @param mixed $type type
     *
     * @return array
     */
    public function search($body, $type = null)
    {
        if (!$type) {
            $type = $this->type;
        }

        $params = [
            'index' => "{$this->project}_{$type}",
            'type'  => $type,
            'body'  => $body
        ];

        $searchClient = $this->getClient();
        return $searchClient->search($params);
    }

    /**
     * searchAll
     *
     * @param string $type   type
     * @param string $source source
     * @param int    $size   window size
     *
     * @return array
     */
    public function searchAll($type = null, $source = '_id', $size = 1000)
    {
        if (!$type) {
            $type = $this->type;
        }

        $params = [
            'index'   => "{$this->project}_{$type}",
            'type'    => $type,
            "scroll"  => "1m",
            '_source' => $source,
            'body'    => [
                "size" => $size
            ],
            'client'  => [ 'ignore' => [400, 404] ],
        ];

        $searchClient = $this->getClient();
        return $searchClient->search($params);
    }

    /**
     * scroll
     *
     * @param string $scrollId scrollId
     *
     * @return array
     */
    public function scroll($scrollId)
    {
        $params = [
            'scroll'    => "1m",
            'scroll_id' => $scrollId
        ];
        $searchClient = $this->getClient();
        return $searchClient->scroll($params);
    }

    /**
     * clearScroll
     *
     * @param string $scrollId scrollId
     *
     * @return array
     */
    public function clearScroll($scrollId)
    {
        $params = [
            'scroll_id' => $scrollId
        ];
        $searchClient = $this->getClient();
        return $searchClient->clearScroll($params);
    }

    /**
     * 用關鍵字搜尋
     *
     * @param string  $schema     schema name or array
     * @param string  $keyword    關鍵字
     * @param array   $filter     過濾條件
     * @param integer $page       page
     * @param integer $limit      limit
     * @param array   $sort       sort
     * @param bool    $counting   是否做關鍵字統計(hotquery) ["auto", "force", "no"]
     * @param bool    $aggs       aggregation
     * @param array   $postfilter 後置過濾條件
     * @param string  $source     只回傳那些 source 的欄位
     *
     * @return array search result
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function goSearch(
        $schema,
        $keyword = null,
        $filter = [],
        $page = 1,
        $limit = 10,
        $sort = [],
        $counting = false,
        $aggs = true,
        $postfilter = [],
        $source = null
    ) {
        // 會再過濾
        $qarr = [
            'keyword'     => $keyword,
            'filter'      => $filter,
            'page'        => $page,
            'limit'       => $limit,
            'sort'        => $sort,
            'aggs'        => $aggs,
            'post_filter' => $postfilter,
            'counting'    => $counting,
            '_source'     => $source,
        ];

        return $this->query($qarr, $schema);
    }

    /**
     * searchTemplate
     *
     * @param string $scriptName script name
     * @param array  $body       body
     * @param string $type       type
     *
     * @return array
     */
    public function searchTemplate($scriptName, $body, $type = null)
    {
        if (!$type) {
            $type = $this->type;
        }

        $scriptId = "{$this->project}_{$scriptName}";
        $params = [
            'index'  => "{$this->project}_{$type}",
            'type'   => $type,
            'body'   => ['id' => $scriptId, 'params' => $body],
            'client' => [ 'ignore' => [400, 404] ],
        ];

        $searchClient = $this->getClient();
        return $searchClient->searchTemplate($params);
    }

    /**
     * setScript
     *
     * @param string $scriptName script name
     * @param string $code       code
     *
     * @return array
     */
    public function putScript($scriptName, $code)
    {
        $scriptId = "{$this->project}_{$scriptName}";
        $params = [
            'id'   => $scriptId,
            'lang' => self::LANG,
            'body' => ['script' => $code]
        ];
        $searchClient = $this->getClient();
        return $searchClient->putScript($params);
    }

    /**
     * getScript
     *
     * @param string $scriptName script name
     *
     * @return array
     */
    public function getScript($scriptName)
    {
        $scriptId = "{$this->project}_{$scriptName}";
        $params = [
            'id'     => $scriptId,
            'lang'   => self::LANG,
            'client' => [ 'ignore' => [400, 404] ],
        ];
        $searchClient = $this->getClient();
        $ret = $searchClient->getScript($params);
        if (isset($ret['script'])) {
            return $ret['script'];
        }
        return $ret;
    }

    /**
     * deleteScript
     *
     * @param string $scriptName script name
     *
     * @return array
     */
    public function deleteScript($scriptName)
    {
        $scriptId = "{$this->project}_{$scriptName}";
        $params = [
            'id'     => $scriptId,
            'lang'   => self::LANG,
            'client' => [ 'ignore' => [400, 404] ],
        ];
        $searchClient = $this->getClient();
        return $searchClient->deleteScript($params);
    }

    /**
     * getScripts By pattern
     *
     * @param string $pattern pattern
     *
     * @return array
     */
    public function getScriptsBy($pattern)
    {
        $ret = [];
        $searchClient = $this->getClient();
        $metadata = $searchClient->getMetadata();
        if (isset($metadata['metadata']['stored_scripts'])) {
            foreach ($metadata['metadata']['stored_scripts'] as $key => $value) {
                if (preg_match($pattern, $key, $matches)) {
                    if ("*" == $this->project) {
                        $ret[$matches[1]][$matches[2]] = $value['source'];
                    } elseif ($matches[1] == $this->project) {
                        $ret[$matches[2]] = $value['source'];
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * getAllScript
     *
     * @return array
     */
    public function getAllScript()
    {
        return $this->getScriptsBy("/^(\w+)_(\w+)$/");
    }

    /**
     * getAllSchema
     *
     * @return array
     */
    public function getAllSchema()
    {
        return $this->getScriptsBy("/^(\w+)_(\w+)\.schema$/");
    }
}
