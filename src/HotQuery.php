<?php
namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;
use GoSearch\ProjectIndex;
use GoSearch\ProjectSearch;

/**
 * 熱門與同音詞庫
 *
 * @author hc_chien <hc_chien>
 */
class HotQuery extends SearchBase
{
    const TYPENAME = "hotquery";

    // for index
    const INDEX_SCHEMAFILE = __DIR__ . "/../config/index/hotqueryIndex.json";

    // for search
    const FUZZYSEARCH_NAME  = "fuzzy";
    const FUZZYSEARCH_SCRIPTFILE = __DIR__ . "/../config/search/fuzzySearch.script";
    const FUZZYSEARCH_UISCHEMAFILE = __DIR__ . "/../config/search/fuzzySearchUI.json";

    // to be removed
    const FUZZYSEARCH_SCHEMAFILE = __DIR__ . "/../config/search/fuzzySearch.json";

    protected $project;
    protected $index;

    /**
     * 建構子
     *
     * @param string $project $project
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @codeCoverageIgnore
     */
    public function __construct($project)
    {
        if (! $project) {
            throw new \Exception("project is required");
        }

        $this->project = $project;
        $this->index = $this->project . "_" . self::TYPENAME;

        /*
            設定在 INDEX_SCHEMAFILE 裡面，如果要改要在 web 工具上改
            $di = \Phalcon\DI\FactoryDefault::getDefault();
            if (!$di) {
            return;
            }
            if ($di->has("config")) {
            $config = $di->get("config");
            $this->settings = (array) $config->elasticSearch->settings;
            }
        */
    }

    /**
     * 同步同音詞庫 (Elastic search)
     *
     * @param integer $days    至少要存在(1)天以上的紀錄
     * @param integer $dicSize 取得最多(6000)筆紀錄
     *
     * @return array
     */
    public function syncDic($days = 1, $dicSize = 6000)
    {
        $queryLog = new QueryLog($this->project);

        // 取得最多 DICSIZE (6000) 筆紀錄, 至少要存在 $days (1) 天以上的紀錄
        $data = $queryLog->getHotWords($dicSize, "query,matches,count,updateTime", $days);

        if (empty($data)) {
            \GoSearch\Helper\Message::debugLog("[syncDic] 沒有搜尋紀錄");
            return;
        }

        // 轉換同音 (一次做完, 效能加快百倍)
        foreach ($data as $list) {
            $queryArr[] = $list['query'];
        }

        $str         = implode("\t", $queryArr);
        $phoneticArr = explode("\t", SearchHelper::getPhonetic($str));

        // 準備要建索引的資料
        $body = array();
        $ptr  = 0;
        foreach ($data as $list) {
            $phonetic = $phoneticArr[$ptr++];
            $body[]   = [
                         "docid"      => $list['query'],
                         "name"       => $list['query'],
                         "count"      => $list['count'],
                         "matches"    => $list['matches'],
                         "phonetic"   => $phonetic,
                         "updateTime" => $list['updateTime']
                        ];

            \GoSearch\Helper\Message::debugLog("[syncDic] {$this->index} {$list['query']} $phonetic {$list['count']} {$list['matches']}");
        }//end foreach

        return $this->syncHotQueryIndex($body);
    }

    /**
     * syncHotQueryIndex
     *
     * @param mixed $body body
     *
     * @return array
     */
    private function syncHotQueryIndex($body)
    {

        $schema = json_decode(file_get_contents(self::INDEX_SCHEMAFILE), 1);

        // 在 DB 建立 index config
        $project = new Project($this->project);
        $project->setIndex(self::TYPENAME, $schema);

        // 在 ElasticSearch 建立 index
        $indexHandler = new ProjectIndex($this->project);
        $indexHandler->delete(self::TYPENAME);
        $indexHandler->putTemplate(self::TYPENAME, $schema);
        $indexHandler->create(self::TYPENAME);

        // 在 ElasticSearch 建立 search script
        $projectSearch = new ProjectSearch($this->project);
        $ret = $projectSearch->getScript(self::FUZZYSEARCH_NAME);
        if (isset($ret['found']) && $ret['found'] == false) {
            $script = file_get_contents(self::FUZZYSEARCH_SCRIPTFILE);
            $projectSearch->putScript(self::FUZZYSEARCH_NAME, $script);
        }

        // 在 ElasticSearch 建立 search ui schema
        $ret = $projectSearch->getScript(self::FUZZYSEARCH_NAME . ".schema");
        if (isset($ret['found']) && $ret['found'] == false) {
            $schema = json_decode(file_get_contents(self::FUZZYSEARCH_UISCHEMAFILE), 1);
            $projectSearch->putScript(self::FUZZYSEARCH_NAME . ".schema", $schema);
        }

        $params = [
            'index' => $this->index,
            'type'  => self::TYPENAME
        ];

        \GoSearch\Helper\Message::debugLog("[syncHotQueryIndex] bulk indexing: " . count($body) . "筆");
        return $this->getClient()->bulk($body, $params);
    }
}
