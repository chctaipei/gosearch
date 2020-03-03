<?php
namespace GoSearch\Task;

use GoSearch\Admin;
use GoSearch\Cronjob;
use GoSearch\Crontab;
use GoSearch\Daemon;
use GoSearch\QueryLog;
use GoSearch\HotQuery;
use GoSearch\Boost;

/**
 * CronTask
 *
 * 建議執行順序:
 * 1. 匯入主資料                                 taskId = 0
 * 2. 同步 matches (會清除 matches=0 的關鍵字)   taskId = 1
 * 3. 同步同音詞庫 (建清除重建 HotQuery Index)   taskId = 2
 *
 * 建議每周執行一次即可, 頻率太高會使 QueryLog 容易被清空
 * 1. 重置 counter                               taskId = 3
 *
 * @subject("Cronjob")
 *
 * @author("hc_chien <hc_chien@hiiir.com>")
 */
class CronTask extends MainTask
{

    /**
     * testCronstring
     *
     * @param string $string string
     *
     * @return boolean
     */
    private function testCronstring($string)
    {
        if ($string == '') {
            return true;
        }

        try {
            if (Crontab::parse($string) != null) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * 啟動 cron job server
     *
     * @return void
     */
    public function sstartAction()
    {
        Daemon::executeInParallel(
            function () {
                $cronjob = new Cronjob();
                $cronjob->start();
            },
            [],
            1
        );
    }

    /**
     * 啟動 cron job server
     *
     * @return array
     *
     * @subject("啟動 cron job server")
     */
    public function startAction()
    {
        if (!extension_loaded('pcntl')) {
            /*
                $message = shell_exec("php ../gosearch.php Cron sstart 2>/dev/null >/dev/null &");
                $cmd = "php ../gosearch.php Cron sstart";
                $logfile = '/tmp/cronserver.log';
                $exec_cmd = sprintf("%s > /dev/null 2>&1 & echo $! > %s & cat %s", $cmd, $logfile, $logfile);
                exec($exec_cmd);
            */

            return $this->response(500, "cron server 無法啟動，請以 CLI mode 執行");
        }
        $this->sstartAction();
        return $this->response(200, "啟用服務");
    }

    /**
     * 啟動 cron job server
     *
     * @return array
     *
     * @subject("中止 cron job server")
     */
    public function stopAction()
    {
        $cronjob = new Cronjob();
        $cronjob->stop();
        return $this->response(200, "停止服務");
    }

    /**
     * 啟動 cron job server
     *
     * @return array
     *
     * @subject("重啟 cron job server")
     */
    public function restartAction()
    {
        $this->stopAction();
        sleep(1);
        return $this->startAction();
    }

    /**
     * 取得 cron job server 狀態
     *
     * @return array
     *
     * @subject("取得 cron job server 狀態")
     */
    public function statusAction()
    {
        $cronjob = new Cronjob();
        $ret = $cronjob->getServerStatus();
        if (!$ret) {
            return $this->response(200, ['result' => ['status' => 0, 'message' => "服務未啟動"]]);
        }

        return $this->response(200, ['result' => ['status' => 1, 'message' => $ret]]);
    }

    /**
     * 取得系統預設排程列表
     *
     * @return array
     *
     * @subject("取得系統預設排程列表")
     */
    public function listDefaultAction()
    {
        $cronjob = new Cronjob();
        $ret = $cronjob->listDefaultJobs();
        return $this->response(200, ['result' => $ret]);
    }

    /**
     * 取得所有已設定的 cronjob
     *
     * @return array
     *
     * @subject("取得所有已設定的 cronjob")
     */
    public function listAllAction()
    {
        $cronjob = new Cronjob();
        $ret = $cronjob->getAllJobs();
        return $this->response(200, ['result' => $ret]);
    }

    /**
     * 取得專案的 cronjob 設定
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("取得專案的 cronjob 設定")
     *
     * @arg("PROJECT [TASK] [TYPE]")
     */
    public function getProjectAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }

        $cronjob = new Cronjob();
        if (!isset($params[1])) {
            $ret = $cronjob->listJobs($params[0]);
        } else {
            $ret = $this->getTargetJob($params[0], $params[1], $params[2] ?? '');
        }

        return $this->response(200, ['result' => $ret]);
    }

    /**
     * getTargetJob
     *
     * @param string $project project
     * @param string $task    task
     * @param string $type    type
     *
     * @return array
     */
    private function getTargetJob($project, $task, $type = '')
    {
        // 取得 list
        $cronjob = new Cronjob();
        $ret = $cronjob->listJobs($project);

        foreach ($ret as $job) {
            if ($job['task'] == $task) {
                if ($task != "importData") {
                    return $job;
                }

                if ($job['type'] == $type) {
                    return $job;
                }
            }
        }

        return null;
    }

    /**
     * encodeMergeParameter
     *
     * @param string $param    param
     * @param array  $defaults defaults
     *
     * @return array
     */
    private function getJobParameter($param, $defaults)
    {
        $parameter = json_decode($param, 1);
        if ($parameter) {
            foreach ($defaults as $key => $value) {
                if ($value == ":PROJECT:" || $value == ":TYPE:") {
                    continue;
                }

                if (isset($parameter[$key])) {
                    $defaults[$key] = $parameter[$key];
                }
            }
        }

        return $defaults;
    }

    /**
     * 新增或更新一筆 project cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("專案新增一筆 cronjob")
     *
     * @arg("PROJECT TASK JSON_PARAMETER CRONSTRING [TYPE]")
     */
    public function updateProjectJobAction($params)
    {
        if (!$this->validateParams($params, 4)) {
            return $this->response(400, "參數錯誤");
        }

        $project    = $params[0];
        $task       = $params[1];
        $param      = $params[2];
        $cronstring = $params[3];
        $type       = $params[4] ?? '';

        // 測試 cronstring
        if (!$this->testCronstring($cronstring)) {
            return $this->response(400, "cronstring [$cronstring] 格式錯誤");
        }

        // 取得 target job
        $target = $this->getTargetJob($project, $task, $type);

        if (!$target) {
            return $this->response(400, "job 不存在");
        }

        // 設定參數
        $data = $target['data'];
        unset($data['placeholder']);
        unset($data['hidden']);
        unset($data['optional']);
        $data['parameter'] = $this->getJobParameter($param, $target['data']['parameter']);
        $dataJson = json_encode($data);

        $cronjob = new Cronjob();

        // 新增
        if (!isset($target["jobid"])) {
            $ret = $cronjob->addJob($task, $dataJson, $cronstring, $project, $type);
            if ($ret) {
                return $this->response(200, '新增成功');
            }
            return $this->response(400, '新增失敗');
        }

        // 更新內容
        $ret = $cronjob->updateCron($target["jobid"], $cronstring, $dataJson);
        if ($ret) {
            return $this->response(200, '更新成功');
        }
        return $this->response(400, '更新失敗, 不存在或內容相同');
    }

    /**
     * activeProject
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("修改 cronjob 啟用狀態")
     *
     * @arg("PROJECT TASK ACTIVATE(0/1) [TYPE]")
     */
    public function activeProjectAction($params)
    {
        $project = $params[0];
        $task    = $params[1];
        $active  = $params[2];
        $type    = $params[3] ?? '';

        // 取得 target job
        $target = $this->getTargetJob($project, $task, $type);

        if (!$target) {
            return $this->response(400, "job 不存在");
        }

        if (!isset($target["jobid"])) {
            $cronjob = new Cronjob();
            $jobid = $cronjob->addJob($task, $target['data'], $target['cronstring'], $project, $type);
        } else {
            $jobid = $target["jobid"];
        }

        $cronjob = new Cronjob();
        $ret = $cronjob->setActive($jobid, $active);
        if ($ret) {
            return $this->response(200, $active ? '啟用完成' : '關閉服務');
        }
        return $this->response(400, '更新失敗, 不存在或內容相同');
    }

    /**
     * 新增一筆 cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("(不限專案)新增一筆 cronjob (系統管理員使用)")
     *
     * @arg("TASK JSON/FILE CRONSTRING [PROJECT] [TYPE]")
     */
    public function addJobAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤");
        }

        $data = $this->fetchParam($params[1]);
        if (!$data) {
            return $this->response(400, "json 格式錯誤");
        }

        $dataJson = json_encode($data);

        if (!$this->testCronstring($params[2])) {
            return $this->response(400, "cronstring 格式錯誤");
        }

        $project = $params[3] ?? '';
        $type    = $params[4] ?? '';

        $cronjob = new Cronjob();
        $ret = $cronjob->addJob($params[0], $dataJson, $params[2], $project, $type);
        if ($ret) {
            return $this->response(200, '新增成功');
        }
        return $this->response(400, '新增失敗');
    }

    /**
     * 刪除 cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除 cronjob (系統管理員使用)")
     *
     * @arg("JOBID")
     */
    public function delJobAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }

        $cronjob = new Cronjob();
        $ret = $cronjob->delJob($params[0]);
        if ($ret) {
            return $this->response(200, '刪除成功');
        }
        return $this->response(400, '刪除失敗');
    }

    /**
     * 取得 cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("取得 cronjob")
     *
     * @arg("JOBID [log offset]")
     */
    public function getJobAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }

        $cronjob = new Cronjob();
        $ret = $cronjob->getJob($params[0]);
        if (count($ret)) {
            $ret = $ret[0];
            $offset = $params[1] ?? 0;
            $ret['log'] = $cronjob->getLogContent($params[0], $offset);
            $ret['offset'] = $offset + strlen($ret['log']);
        }
        return $this->response(200, ['result' => $ret]);
    }

    /**
     * 刪除執行中的 cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除執行中的 cronjob")
     *
     * @arg("JOBID")
     */
    public function killJobAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }

        $cronjob = new Cronjob();
        $ret = $cronjob->killJob($params[0]);
        return $this->response(200, ['result' => $ret]);
    }

    /**
     * 立即執行 cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("立即執行 cronjob")
     *
     * @arg("JOBID")
     */
    public function runJobAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }

        try {
            $cronjob = new Cronjob();
            $ret = $cronjob->setInstant($params[0]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }

        if ($ret) {
            return $this->response(200, "已排入");
        }

        return $this->response(400, "無法執行");
    }

    /**
     * 取得 cronjob
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("修改 cronjob 執行時間與內容 (系統管理員使用)")
     *
     * @arg("JOBID CRONSTRING [JSON/FILE]")
     */
    public function updateJobAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤");
        }

        if (!$this->testCronstring($params[1])) {
            return $this->response(400, "cronstring 格式錯誤");
        }

        $dataJson = null;
        if (isset($params[2])) {
            $data = $this->fetchParam($params[2]);
            if (!$data) {
                return $this->response(400, "json 格式錯誤");
            }
            $dataJson = json_encode($data);
        }

        try {
            $cronjob = new Cronjob();
            $ret = $cronjob->updateCron($params[0], $params[1], $dataJson);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }

        if ($ret) {
            return $this->response(200, '更新成功');
        }
        return $this->response(400, '更新失敗, 不存在或內容相同');
    }

    /**
     * 重置 counter
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("重設熱門關鍵字的計數器")
     *
     * @arg("PROJECT")
     *
     * @cron("每周一早上四點")
     */
    public function resetCounterAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }
        $project = $params['project'] ?? $params[0];
        try {
            $queryLog = new QueryLog($project);
            $queryLog->resetCounter();
            return $this->response(200, '重置完成');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * 同步同音詞庫
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("同步同音詞庫, Query log 建立時間必須大於預設 1 天, 同步筆數預設 6000 筆")
     *
     * @arg("PROJECT [DAYS] [COUNT]")
     *
     * @cron("每周一早上三點")
     */
    public function syncDicAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤");
        }

        $project = $params['project'] ?? $params[0];
        $days = $params['days'] ?? $params[1] ?? 1;
        $count = $params['count'] ?? $params[2] ?? 6000;

        if (empty($days)) {
            $days = 1;
        }

        if (empty($count)) {
            $count = 6000;
        }

        try {
            $hotquery = new HotQuery($project);
            $hotquery->syncDic($days, $count);
            return $this->response(200, '同步完成');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * 同步 matches, 會將 matches=0 的關鍵字刪除
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("同步熱門關鍵字的顯示數量, 於主資料匯入後執行 (非必要)")
     *
     * @arg("PROJECT TYPE SCRIPTNAME [TAG] [BODY]")
     *
     * @cron("每周三早上五點")
     */
    public function syncMatchesAction($params)
    {
        if (!$this->validateParams($params, 3)) {
            return $this->response(400, "參數錯誤");
        }

        $project = $params['project'] ?? $params[0];
        $type    = $params['type'] ?? $params[1];
        $scriptName = $params['script'] ?? $params[2];
        $keywordTag = $params['tag'] ?? $params[3] ?? 'query';
        $body = $params['body'] ?? $params[4] ?? null;

        if (empty($keywordTag)) {
            $keywordTag = 'query';
        }

        if ($body) {
            $body = $this->fetchParam($body);
        }

        try {
            $queryLog = new QueryLog($project);
            $queryLog->syncMatches($scriptName, $type, $keywordTag, $body);
            return $this->response(200, '同步完成');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * 點擊分數更新
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("同步點擊排序")
     *
     * @arg("PROJECT TYPE")
     *
     * @cron("每30分鐘")
     */
    public function syncBoostAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params['project'] ?? $params[0];
        $type    = $params['type'] ?? $params[1];
        try {
            $boost = new Boost($project);
            $boost->syncScores($type);
            return $this->response(200, '同步完成');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * 點擊數減半
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("點擊次數減半")
     *
     * @arg("PROJECT")
     *
     * @cron("每天一次")
     */
    public function reduceCountAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $project = $params['project'] ?? $params[0];
        try {
            $boost = new Boost($project);
            $boost->reduceCount();
            return $this->response(200, '更新完成');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            \GoSearch\Helper\Message::exceptionLog($e);
            return $this->response(500, $message);
        }
    }

    /**
     * test
     *
     * @param array $params parameter
     *
     * @return array
     *
     * @subject("測試")
     *
     * @arg("PROJECT TYPE")
     */
    public function testAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }
        $project = $params['project'] ?? $params[0];
        $type    = $params['type'] ?? $params[1];
        $boost = new Boost($project);
        $boost->createTable();
        for ($i = 1; $i < 100000; $i++) {
            $boost->insertRecord($i, 'iphone');
            $boost->insertRecord($i, 'apple');
        }
        return;

        $boost = new Boost($project);
        $boost->syncIndex($type);
    }
}
