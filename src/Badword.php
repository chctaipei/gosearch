<?php
namespace GoSearch;

use GoSearch\QueryLog;

/**
 * 不良關鍵字
 *
 * @author hc_chien <hc_chien>
 */
class Badword extends DBFactory
{
    const TABLENAME = "badword";

    protected $table;
    protected $schemaFile = __DIR__ . "/../config/db/badword.sql";
    protected $badKeyword = ['pattern' => ['/^(sex)$|^(av)$|^[a-z0-9]$/i'], 'keyword' => []];

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
     * readCache
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function readCache()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $cache = $di->get("cache");
        return $cache->get($this->table);
    }

    /**
     * saveCache
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function saveCache()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        $cache = $di->get("cache");
        return $cache->save($this->table, $this->badKeyword, 0);
    }

    /**
     * loadDB
     *
     * @return void
     */
    private function loadData()
    {
        $data = $this->readCache();
        if ($data) {
            $this->badKeyword = $data;
            return;
        }

        $sql = "SELECT string,type,createTime FROM {$this->table}";
        $ret = $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
        foreach ($ret as $data) {
            $string = $data['string'];
            if ($data['type'] == 0) {
                $this->badKeyword['keyword'][$string] = $data['createTime'];
            } else {
                $this->badKeyword['pattern'][] = $string;
            }
        }
        $this->saveCache();
    }

    /**
     * 檢查不良關鍵字
     *
     * @return array
     */
    public function listing()
    {
        $this->loadData();
        return [
            'pattern' => $this->badKeyword['pattern'],
            'keyword' => array_keys($this->badKeyword['keyword'])
        ];
    }

    /**
     * 新增不良關鍵字
     *
     * @param string $pattern pattern
     *
     * @return boolean
     */
    public function insert($pattern)
    {
        $this->loadData();
        $pattern = trim(mb_strtolower($pattern, 'utf-8'));
        if ($pattern == '') {
            // empty
            return false;
        }

        if (substr($pattern, 0, 1) == '/') {
            if (in_array($pattern, $this->badKeyword['pattern'])) {
                return false;
            }
            $this->badKeyword['pattern'][] = $pattern;
            $type = 1;
        } else {
            if (isset($this->badKeyword['keyword'][$pattern])) {
                return false;
            }
            $this->badKeyword['keyword'][$pattern] = 1;
            $type = 0;
        }

        $this->connection->execute(
            "INSERT IGNORE INTO {$this->table} SET string=:string, type=:type, createTime=NOW()",
            [
                'string' => $pattern,
                'type'   => $type,
            ]
        );
        $this->saveCache();

        // 將熱門設定不良字移除... $project = ?
        // $queryLog = new QueryLog($project);
        // $queryLog->updateCount($pattern, 0);
        return true;
    }

    /**
     * 刪除不良關鍵字
     *
     * @param string $pattern pattern
     *
     * @return boolean
     */
    public function delete($pattern)
    {
        $this->loadData();
        $update = false;
        $pattern = trim(mb_strtolower($pattern, 'utf-8'));
        if (substr($pattern, 0, 1) == '/') {
            $ret = array_diff($this->badKeyword['pattern'], [$pattern]);
            if ($ret != $this->badKeyword['pattern']) {
                $this->badKeyword['pattern'] = $ret;
                $update = true;
            }
        } else {
            if (isset($this->badKeyword['keyword'][$pattern])) {
                unset($this->badKeyword['keyword'][$pattern]);
                $update = true;
            }
        }

        if ($update) {
            $this->connection->execute(
                "DELETE FROM {$this->table} WHERE string=:string",
                ['string' => $pattern]
            );
            $this->saveCache();
        }

        return $update;
    }

    /**
     * 檢查不良關鍵字
     *
     * @param string $query query
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function isBad($query)
    {
        $this->loadData();
        $query = trim(mb_strtolower($query, 'utf-8'));
        if (isset($this->badKeyword['keyword'][$query])) {
            return true;
        }

        foreach ($this->badKeyword['pattern'] as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                return true;
            }
        }

        return false;
    }
}
