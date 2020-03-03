<?php
namespace GoSearch\Task;

use GoSearch\Admin;
use GoSearch\ProjectSearch;
use GoSearch\SearchClient;
use GoSearch\QueryLog;
use GoSearch\HotQuery;
use GoSearch\Badword;

/**
 * OtherTask
 *
 * @subject("其他")
 *
 * @author("hc_chien <hc_chien@hiiir.com>")
 */
class ToolTask extends MainTask
{

    /**
     * 餵 query log
     *
     * @param array $params parameter
     *
     * @return void
     *
     * @subject("餵 query log 檔案")
     *
     * @arg("PROJECT FILENAME")
     *
     * @SuppressWarnings("PHPMD.ShortVariable")
     */
    public function feedQueryAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return;
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');

            $queryLog = new QueryLog($params[0]);
            $fp = fopen($params[1], "r");
            while ($buf = fgets($fp)) {
                if (strstr($buf, ",")) {
                    @list($query, $matches) = @explode(",", $buf);
                } else {
                    $query   = $buf;
                    $matches = 0;
                }
                $queryLog->insertQuery($query, intval($matches));
            }
            fclose($fp);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 餵單筆 query
     *
     * @param array $params parameter
     *
     * @return void
     *
     * @subject("輸入單筆 query & matches")
     *
     * @arg("PROJECT QUERY MATCHES")
     */
    public function insertQueryAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return;
        }

        try {
            $queryLog = new QueryLog($params[0]);
            $queryLog->insertQuery($params[1], $params[2]);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * list Badword
     *
     * @param mixed $params params
     *
     * @return array
     *
     * @subject("列出壞詞清單")
     *
     * @arg("PROJECT")
     */
    public function listBadwordAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        try {
            $badword = new Badword($params[0]);
            $ret = $badword->listing();
            return $this->response(200, $ret);
        } catch (\PDOException $e) {
            return $this->response(400,  "未完成初始化\n");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * insert Badword
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("新增壞詞 (不會在熱門關鍵字出現)")
     *
     * @arg("PROJECT BADWORD")
     */
    public function insertBadwordAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $badword = new Badword($params[0]);
        $ret = $badword->insert($params[1]);
        if ($ret) {
            return $this->response(200, '更新成功');
        }
        return $this->response(400, '內容相同或不存在');
    }

    /**
     * delete Badword
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("刪除壞詞")
     *
     * @arg("PROJECT BADWORD")
     */
    public function deleteBadwordAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $badword = new Badword($params[0]);
        $ret = $badword->delete($params[1]);
        if ($ret) {
            return $this->response(200, '刪除成功');
        }
        return $this->response(404, '資料不存在');
    }

    /**
     * filterHotwordAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("過濾熱門詞裡面的壞詞")
     *
     * @arg("PROJECT")
     */
    public function filterHotwordAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $total = 0;
        $badword = new Badword($params[0]);
        $ret = $badword->listing();
        $queryLog = new QueryLog($params[0]);

        foreach ($ret['pattern'] as $pattern) {
            $total = $queryLog->updateCount($pattern, 0);
        }

        foreach ($ret['keyword'] as $keyword) {
            $total += $queryLog->updateCount($keyword, 0);
        }
        return $this->response(200, "共 $total 筆清除成功");
    }

    /**
     * testAction
     *
     * @param mixed $params params
     *
     * @return array
     *
     * @SuppressWarnings("PHPMD.ShortVariable")
     */
    public function testAction($params = [])
    {
        global $di;

        $r = new \GoSearch\Redis();
        var_dump($r->get('badword'));
        return;

        // $di = \Phalcon\DI\FactoryDefault::getDefault();
        $logger = $di->get("logger");
        $logger->info($params[0]);
       
        $cache = $di->get("cache");
        $cache->removeUsersession('aaa');
        return;
        // $cache->_connect();
        // $cache->save('test', ['hello' => 1, 'world' => 2], $ttl = 0);
        // var_dump($cache->exists('test'));
        var_dump($cache->get('badword'));
        $redis = $cache->getRedis();
        $list = $redis->keys("_PHCR*");
        var_dump($list);
        $list = $cache->keys("user");
        var_dump($list);
        // var_dump($cache->keys("_PHCR48065aef8a651d81b453e4de74180261"));
    }
}
