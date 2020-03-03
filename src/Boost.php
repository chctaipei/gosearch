<?php
namespace GoSearch;

use GoSearch\QueryLog;
use PDO;

/**
 * Boost 點擊排序
 *
 * 1. 每次 click 的工作
 *    function: insertRecord($docid, $query)
 *    a. inset into ... on duplicated key update click = click+1, status = 0, updateTime=NOW()
 *
 * 2. 每 30 分鐘同步 click score 到 search engine (更新週期可以拉長到一小時或更久一次)
 *    function: syncScores($type)
 *    a. 掃描 table, 找出 status=0 的紀錄
 *    b. 更新 search engine index 的 score
 *    c. 更新 table (set status=1)
 *    d. 刪除 table (count=0 and status=1)
 *
 * 3. 每日 click count 減半, 將 15 天未更新的 query 歸零
 *    function: reduceCount()
 *    a. updateTime > 15 天 => count 歸 0
 *    b. count 減半, set status=0
 *
 * 分數公式:
 *    (int) round(log1p($count) * self::PVALUE);
 *    PVALUE 越大，PVALUE 越大，click count 的影響越大, 例如: 1000 => 精確到小數點以下三位
 *
 * 採用 hotword ?
 *    優點：減少 table size
 *    缺點：
 *      a. 需做 hotword 的 cache
 *      b. 效果可能有限
 **/
class Boost extends DBFactory
{
    const TABLENAME = "boost";
    // 須滿足前 3000 筆 熱門關鍵字
    const HOTWORD_LIMIT = 3000;
    // 需在 15 日內有更新
    const EXPIRE_DAYS = 15;
    // 精確到小數點三位
    const PVALUE = 1000;
    // 每個 id 保留最多幾筆 query
    const MAX_QUERIES_PER_ID = 20;

    protected $table;
    protected $schemaFile = __DIR__ . "/../config/db/boost.sql";

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
     * removeExpired
     *
     * @return void
     */
    private function removeExpired()
    {
        $this->connection->execute("DELETE FROM {$this->table} WHERE count = 0 AND status = 1");
    }

    /**
     * 計算分數
     *
     * @param int $count count
     *
     * @return int
     */
    private function calc($count)
    {
        return (int) round(log1p($count) * self::PVALUE);
    }

    /**
     * updateStatus
     *
     * @param array $items items
     *
     * @return void
     */
    private function updateStatus($items)
    {
        foreach ($items as $item) {
            $arr[] = $item['docid'];
        }

        $inArr = str_repeat('?,', count($arr) - 1) . '?';
        $this->connection->execute(
            "UPDATE {$this->table} SET status = 1 WHERE docid IN ($inArr)",
            $arr
        );
    }

    /**
     * updateIndex
     *
     * @param string $type  type
     * @param array  $items items
     *
     * @return void
     */
    private function updateIndex($type, $items)
    {
        if (!$type || !$items) {
            return;
        }

        $projectIndex = new ProjectIndex($this->project);
        $ret = $projectIndex->bulkUpdate($items, $type);
        return $ret;
    }

    /**
     * 保留分數最高的前 20 筆 query
     *
     * @param array $data data
     *
     * @return array
     */
    private function filterBoost($data)
    {
        $arr = $data['boost'];
        usort(
            $arr,
            function ($itema, $itemb) {
                return $itemb['score'] - $itema['score'];
            }
        );

        $data['boost'] = array_slice($arr, 0, self::MAX_QUERIES_PER_ID);
        return $data;
    }

    /**
     * 每半小時同步 click count 到 index
     *
     * @param string $type type
     *
     * @return void
     */
    public function syncScores($type)
    {
        if (!$type) {
            return;
        }

        // 當有其中一筆更新時，要連帶將其他筆記錄撈出來
        $sql = "SELECT docid, query, count,status FROM {$this->table} WHERE docid in (SELECT distinct(docid) from {$this->table} WHERE status = 0)";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $items = [];
        $count = 0;
        $total = 0;
        $docid = null;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            if ($row['docid'] != $docid) {
                if ($docid !== null) {
                    $items[] = $this->filterBoost($boost);
                    if ($count++ >= 1000) {
                        \GoSearch\Helper\Message::debugLog("[Boost::syncScores] $total");
                        $this->updateIndex($type, $items);
                        $this->updateStatus($items);
                        $count = 0;
                        $items = [];
                    }
                    $total++;
                }

                $docid = $row['docid'];
                $boost = [];
                $boost['docid'] = $docid;
                $boost['boost'] = [];
            }

            if ($row['count'] > 0) {
                $boost['boost'][] = ['score' => $this->calc($row['count']), 'keyword' => $row['query']];
            }
        }//end while

        if ($total) {
            $items[] = $this->filterBoost($boost);
            \GoSearch\Helper\Message::debugLog("[Boost::syncScores] $total");
            $this->updateIndex($type, $items);
            $this->updateStatus($items);
            $this->removeExpired();
        }
    }

    /**
     * 每日 click count 減半, 超過 15 日未更新的歸零
     *
     * @return void
     */
    public function reduceCount()
    {
        \GoSearch\Helper\Message::debugLog("[Boost::reduceCount]");

        // 太久未更新
        $days = self::EXPIRE_DAYS;
        $this->connection->execute(
            "UPDATE {$this->table} SET count=0 WHERE updateTime <= SUBDATE(current_time, INTERVAL $days DAY)"
        );

        // count 減半, 用 FLOOR 讓 count=1 歸零
        $this->connection->execute(
            "UPDATE {$this->table} SET count=FLOOR(count/2), status = 0"
        );
    }

    /**
     * 紀錄 docid query
     *
     * @param string $docid docid
     * @param string $query 關鍵字
     *
     * @return boolean
     */
    public function insertRecord($docid, $query)
    {
        $query = SearchHelper::filterQuery($query, true);
        if ('' == $query || empty($docid)) {
            return false;
        }

        $this->connection->execute(
            "INSERT INTO {$this->table} (docid, query, count, status, createTime, updateTime) VALUES (:docid, :query, 1, 0, NOW(), NOW()) ON DUPLICATE KEY UPDATE count = count + 1, updateTime = NOW(), status = 0",
            ["docid" => $docid, "query" => $query]
        );

        return $this->connection->affectedRows();
    }

    /**
     * test
     *
     * @param string $query query
     *
     * @return void
     */
    public function test($query = 'apple')
    {
        for ($i = 0; $i < 100000; $i++) {
            $this->insertRecord($i, $query);
        }
    }
}
