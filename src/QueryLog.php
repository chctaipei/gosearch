<?php
namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;
use GoSearch\Badword;
use GoSearch\Admin;

/**
 * 利用 query log 產生熱門關鍵字,同音詞庫
 *
 * @author hc_chien <hc_chien>
 */

/**
 * 利用 query log 產生熱門關鍵字,同音詞庫
 *
 * Db: mall-search
 * Table: $project_HotQuery
 * +----+-----------+-------+---------+---------------------+---------------------+
 * | id | query     | count | matches | createTime          | updateTime          |
 * +----+-----------+-------+---------+---------------------+---------------------+
 * |  0 | NULL      |  5565 |       0 | 2015-10-15 00:00:00 | 0000-00-00 00:00:00 |
 * |  1 | 防潮劑    |     1 |       0 | 0000-00-00 00:00:00 | 2015-10-15 16:07:30 |
 * +----+-----------+-------+---------+---------------------+---------------------+
 *
 * 說明:
 *     id 0:
 *       count: 紀錄指標
 *       create time: counter 重置的時間
 *
 *     id 1~n:
 *       count: counter
 *       query: 關鍵字
 *       matches: 搜尋結果(數量)
 *       createTime: 新增的時間
 *       updateTime: 更新的時間
 *
 *    - 要記錄常用字, 預留空間至少要有預期數量兩倍以上
 *    - 分析 createTime & updateTime, 刪除短期熱門字(非真正熱門)
 *    - 定期重置 counter
 *    - 演算法參考: http://www.google.com/patents/US7533414
 */
class QueryLog extends DBFactory
{
    const TABLENAME = "hotquery";
    protected $debug = false;

    protected $table;
    protected $schemaFile = __DIR__ . "/../config/db/hotquery.sql";

    // 保留空間至少為常用詞的兩倍以上
    private $tableSize = 10000;

    // 若有重置機制, 就不需設上限
    private $maxScore = 0;

    // QueryLog 於星期一上午四點重置
    private $checkWeekday = 1;

    // 上午四點
    private $checkHour = 4;

    protected $badKeyword = ['pattern' => ['/^(sex)$|^(av)$|^.$/i'], 'keyword' => []];

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
        $this->project = strtolower($project);
        $this->table = $this->project . "_" . self::TABLENAME;
        parent::__construct();
    }

    /**
     * Set Table Size
     *
     * @param integer $tableSize table size
     *
     * @return void
     */
    public function setTableSize($tableSize)
    {
        $this->tableSize = $tableSize;
    }

    /**
     * Set max score
     *
     * @param integer $maxScore max score
     *
     * @return void
     */
    public function setMaxScore($maxScore)
    {
        $this->maxScore = $maxScore;
    }

    /**
     * Set check weekday & time
     *
     * @param integer $checkWeekday weekday (0~6 => sun~sat)
     * @param integer $checkHour    hour (0~23)
     *
     * @return void
     */
    public function setCheckTime($checkWeekday, $checkHour)
    {
        $this->checkWeekday = $checkWeekday;
        $this->checkHour    = $checkHour;
    }

    /**
     * Initialize table
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function initTable()
    {
        for ($id = 0; $id <= $this->tableSize; $id++) {
            $arr[] = "($id, 1)";
        }

        $query = "INSERT IGNORE INTO {$this->table} (id, count) VALUES " . implode(",", $arr);
        $this->connection->execute($query);
        \GoSearch\Helper\Message::debugLog("[initTable] INSERT IGNORE INTO {$this->table}");
        return $this->connection->affectedRows() > 0;
    }

    /**
     * 重設 counter
     *
     * @return void
     */
    public function resetCounter()
    {
        $this->connection->begin();

        // 除去 count = 1 或 30 天沒更新的的 query
        $this->connection->execute("UPDATE {$this->table} SET query=null WHERE id!=0 AND (count=1 OR updateTime <= SUBDATE(current_time, INTERVAL 30 DAY))");

        // 重設所有的 count = 1
        $this->connection->execute("UPDATE {$this->table} SET count=1 WHERE id!=0");

        // 設定本次檢查日期
        $this->connection->execute("UPDATE {$this->table} SET createTime=CURRENT_DATE() WHERE id=0");

        $this->connection->commit();

        \GoSearch\Helper\Message::debugLog("[resetCounter] reset counter {$this->table}");
    }

    /**
     * 檢查是否需要重設 Counter (每周一早上四點)
     *
     * 可考慮增加條件: updateTime <= SUBDATE(current_time, INTERVAL 3 DAY);
     * 去除掉 3天沒更新的 keyword
     *
     * @return boolean
     */
    private function checkCounter()
    {
        // 檢查時間是否為 Monday 4:00am
        $time = explode(" ", date('N H'));
        if ($time[0] != $this->checkWeekday || $time[1] != $this->checkHour) {
            return false;
        }

        // 檢查今天是否已經做過
        if ($this->connection->fetchOne(
            "SELECT createTime FROM {$this->table} WHERE id=0 AND createTime=CURRENT_DATE()"
        )) {
            return false;
        }

        return true;
    }

    /**
     * 如果存在則將 count+1
     *
     * @param string  $query   關鍵字
     * @param integer $matches 符合數量
     *
     * @return boolean
     */
    private function incCountByQuery($query, $matches)
    {
        $this->connection->begin();
        $ret = $this->connection->fetchOne(
            "SELECT count FROM {$this->table} WHERE query=:query",
            \Phalcon\Db::FETCH_ASSOC,
            ['query' => "$query"]
        );

        if ($ret) {
            // \GoSearch\Helper\Message::debugLog("[incCountByQuery] {$this->table} 找到符合 $query 的資料 ({$ret['count']})");
            if (($this->maxScore == 0) || ($ret['count'] < $this->maxScore)) {
                $sql = "UPDATE {$this->table} SET count=count+1,matches=:matches,updateTime=NOW() WHERE query=:query";
                $this->connection->execute($sql, ['query' => $query, 'matches' => $matches]);
                $this->connection->commit();
                return true;
            }

            $sql = "UPDATE {$this->table} SET matches=:matches,updateTime=NOW() WHERE query=:query";
            $this->connection->execute($sql, ['query' => $query, 'matches' => $matches]);
            $this->connection->commit();
            return true;
        }//end if

        $this->connection->commit();
        // \GoSearch\Helper\Message::debugLog("[incCountByQuery] {$this->table} 沒有符合 $query 的資料");
        return false;
    }

    /**
     * 如果不存在則將目前指標的 count-1 如果 count=1 則取代
     *
     * @param string  $query   關鍵字
     * @param integer $matches 符合數量
     *
     * @return void
     */
    private function decCountByQuery($query, $matches)
    {
        if ($this->debug) {
            $ret         = $this->connection->fetchOne("SELECT count FROM {$this->table} WHERE id=0", \Phalcon\Db::FETCH_ASSOC);
            $this->oldId = $ret['count'];
        }

        $sql = <<<EOF
UPDATE {$this->table} AS a, {$this->table} AS b SET
 a.query=IF(a.count>1,a.query,:query),
 a.matches=IF(a.count>1,a.matches,:matches),
 a.count=IF(a.count>1,a.count-1,1),
 a.createTime=IF(a.count>1,a.createTime,NOW())
 WHERE a.id=b.count AND b.id=0
EOF;
        $this->connection->execute($sql, ['query' => $query, "matches" => $matches]);
    }

    /**
     * 紀錄 query
     *
     * @param string  $query   關鍵字
     * @param integer $matches 符合數量
     *
     * @return boolean
     */
    public function insertQuery($query, $matches)
    {
        if ($this->checkCounter()) {
            // 重設 counter
            $this->resetCounter();
        }

        $query = SearchHelper::filterQuery($query, true);
        if ('' == $query || $matches == 0) {
            // \GoSearch\Helper\Message::debugLog("[insertQuery] query=[$query] matches=[$matches] 條件不符合");
            return false;
        }

        $badWord = new Badword($this->project);

        // 不良關鍵字
        if ($badWord->isBad($query)) {
            // \GoSearch\Helper\Message::debugLog("[insertQuery] 不良關鍵字: $query");
            return false;
        }

        // 1. 如果存在則將 count+1
        if ($this->incCountByQuery($query, $matches)) {
            return true;
        }

        $this->connection->begin();

        // 2. 如果不存在則將目前指標的 count-1 如果 count=1 則取代
        $this->decCountByQuery($query, $matches);


        // 3. 將指標往下移
        $this->connection->execute(
            "UPDATE {$this->table} AS b SET b.count=IF(b.count>=:table_size,1,b.count+1) WHERE b.id=0",
            ["table_size" => $this->tableSize]
        );

        if ($this->debug) {
            $ret         = $this->connection->fetchOne("SELECT count FROM {$this->table} WHERE id=0", \Phalcon\Db::FETCH_ASSOC);
            $this->newId = $ret['count'];
            \GoSearch\Helper\Message::debugLog("[insertQuery] {$this->table} 移動指標 {$this->oldId} -> {$this->newId}");
        }

        $this->connection->commit();

        return $this->connection->affectedRows();
    }

    /**
     * updateCount (可以用 regular expression, /^[a-z1-9].$/)
     *
     * @param string $query query
     * @param int    $count count
     *
     * @return boolean
     */
    public function updateCount($query, $count)
    {
        if (substr($query, 0, 1) == '/') {
            $sql = "UPDATE {$this->table} SET count = :count WHERE query REGEXP :query";
            $query = strtok($query, '/');
        } else {
            $sql = "UPDATE {$this->table} SET count = :count WHERE query = :query";
        }

        $this->connection->execute($sql, ['query' => $query, "count" => $count]);
        return $this->connection->affectedRows();
    }

    /**
     * 自動完成的建議
     *
     * @param string  $query query string
     * @param integer $limit 筆數
     *
     * @return array
     */
    public function getSuggestion($query, $limit = 10)
    {
        $query = SearchHelper::filterQuery($query);
        if ('' == $query) {
            return [];
        }

        $sql = "SELECT query,matches FROM {$this->table} WHERE query LIKE :query AND id!=0 ORDER BY count DESC, createTime ASC LIMIT $limit";

        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['query' => "$query%"]);
    }

    /**
     * 大家都在找
     *
     * 若不想要短期熱門的結果(熱門排名不穩定)
     * 則可增加條件: createTime <= SUBDATE(current_time, INTERVAL 1 DAY);
     * 要求存在時間一天以上的關鍵字才輸出排名
     *
     * @param string $limit  筆數
     * @param string $column 輸出欄位
     * @param string $days   建立時間要超過幾天, 預設 0
     *
     * @return array
     */
    public function getHotWords($limit = 10, $column = "query,matches", $days = 0)
    {
        $limit = intval($limit);
        $days  = intval($days);
        $add = '';
        if ($days > 0) {
            $add = "AND createTime <= SUBDATE(current_time, INTERVAL $days DAY)";
        }

        $sql = <<<EOF
SELECT $column FROM {$this->table} WHERE id!=0 AND count>1 $add
 ORDER BY count DESC, createTime ASC LIMIT $limit
EOF;

        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
    }

    /**
     * 同步資料表的 matches
     * 這個功能只有用在當第一次將關鍵字上載到 QueryLog
     * 準備建立同音模糊搜尋之前, 且商品搜尋引擎資料已經建立好
     *
     * @param string $scriptName scriptName
     * @param string $type       type
     * @param string $keywordTag keywordTag
     * @param array  $body       body
     *
     * @return array
     */
    public function syncMatches($scriptName, $type, $keywordTag = 'query', $body = [])
    {
        if (empty($body)) {
            $body = [
                'from' => 0,
                'size' => 0
            ];
        }

        if (!$keywordTag) {
            $keywordTag = 'query';
        }

        $searchClient = new ProjectSearch($this->project);
        $arr = $searchClient->getScript($scriptName);
        if (is_array($arr) && $arr['found'] == false) {
            throw new \Exception("$scriptName 不存在或內容錯誤");
        }

        \GoSearch\Helper\Message::debugLog("[syncMatches] fetch from {$this->table}");
        $sql = "SELECT query FROM {$this->table} WHERE id!=0 AND count>1";
        $ret = $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
        // $ret = [[ 'query' => '電視'], ['query' => '冰箱']];
        // \GoSearch\Helper\Message::debugLog("$scriptName, $type, $keywordTag");
        $pool = [];
        \GoSearch\Helper\Message::debugLog("[syncMatches] search by script:{$scriptName} tag:{$keywordTag} type:{$type}");
        $count = 0;
        foreach ($ret as $data) {
            $keyword = $data['query'];
            $body[$keywordTag] = $keyword;
            $ret = $searchClient->searchTemplate($scriptName, $body, $type);
            if (++$count % 100 == 0) {
                \GoSearch\Helper\Message::debugLog("[syncMatches] {$count} records have searched");
            }
            $pool[$keyword] = $ret['hits']['total'];
        }

        // 更新 matches
        $sql1 = "UPDATE {$this->table} SET matches = :matches WHERE query = :query";

        // 如果 matches = 0, 則將 count 調為 0
        $sql2 = "UPDATE {$this->table} SET count = 0 WHERE query = :query";

        foreach ($pool as $query => $matches) {
            \GoSearch\Helper\Message::debugLog("[syncMatches] $query $matches");

            if ($matches > 0) {
                $this->connection->execute($sql1, ['query' => $query, "matches" => $matches]);
                continue;
            }

            $this->connection->execute($sql2, ['query' => $query]);
        }
    }
}
