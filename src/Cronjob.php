<?php
namespace GoSearch;

use GoSearch\Project;
use GoSearch\Crontab;
use GoSearch\Daemon;
use GoSearch\HttpServer;

/**
 * Class: Cronjob
 *
 * 1. 每秒檢查非週期性的 job (cronstring = '', status = 0)
 * 2. 每30秒檢查週期性的 job (cronstring != '', status = 0)
 *
 * @see DBFactory
 */
class Cronjob extends DBFactory
{
    const TABLENAME = "cronjob";

    const STATUS_WAITING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_STOPPED = 2;
    const STATUS_INSTANT = 3;
    // const STATUS_FAILED  = 4;
    // 一次性的立即執行
    const INACTIVE  = 0;
    const ACTIVE    = 1;

    const MAX_EXEC_TIME = 7200;
    const MAX_PARALLEL_JOB = 10;

    // PORT for HTTP
    // const PORT = 8090;
    // 不可使用 /tmp 參考:
    // https://stackoverflow.com/questions/43918979/php-way-to-get-the-true-tmp-path
    protected $logDir     = __DIR__ . "/../log/";
    protected $pidFile    = __DIR__ . "/../log/gosearch.pid";
    protected $statusFile = __DIR__ . "/../log/gosearch.status";

    protected $table;
    protected $schemaFile = __DIR__ . "/../config/db/cronjob.sql";
    protected $jobFile = __DIR__ . "/../config/jobs.yml";
    static private $lock;
    protected $eventloop;
    protected $timer;
    protected $jobcount = 0;
    protected $startTime = 0;
    protected $routing = [];
    protected $port = 4321;
    protected $jobBoard = [];

    /**
     * 建構子
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function __construct()
    {
        if (!defined('SIGUSR1')) {
            define('SIGUSR1', 10);
        }

        if (!defined('SIGTERM')) {
            define('SIGTERM', 15);
        }

        $this->table = self::TABLENAME;
        parent::__construct();

        $di = \Phalcon\DI\FactoryDefault::getDefault();
        if (!$di) {
            return;
        }

        if ($di->has("config")) {
            $config = $di->get("config");
            $this->logDir = $config->gosearch->application->logDir ?? $config->application->logDir ?? null;
            if ($this->logDir == null) {
                return;
            }
            $this->pidFile = $this->logDir . "/gosearch.pid";
            $this->statusFile = $this->logDir . "/gosearch.status";

            if (!isset($GLOBALS['_LOGFILE'])) {
                $GLOBALS['_LOGFILE'] = $this->logDir . "/cron.log";
            }

            $this->port = $config->gosearch->http->port ?? $config->http->port;
        }
    }

    /**
     * listDefaultJobs
     *
     * @return array
     */
    public function listDefaultJobs()
    {
        $jobList = yaml_parse_file($this->jobFile);
        return $jobList;
    }

    /**
     * addJob
     *
     * @param string $task       task
     * @param string $data       data
     * @param string $cronstring cronstring
     * @param string $project    project
     * @param string $type       type
     *
     * @return jobId
     */
    public function addJob($task, $data, $cronstring, $project = '', $type = '')
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        $sql = <<<EOF
INSERT INTO {$this->table} (task, data, cronstring, project, type, nextExecTime) 
VALUES (:task, :data, :cronstring, :project, :type, :nextExecTime)
EOF;
        $this->connection->execute(
            $sql,
            [
                'task'         => $task,
                'data'         => $data,
                'cronstring'   => $cronstring,
                'project'      => $project,
                'type'         => $type,
                'nextExecTime' => ($cronstring == "") ? null : Crontab::getNextTime($cronstring)
            ]
        );

        return $this->connection->lastInsertId();
    }

    /**
     * updateCron
     *
     * @param int    $jobid      jobid
     * @param string $cronstring cronstring
     * @param string $data       data
     *
     * @return boolean
     */
    public function updateCron($jobid, $cronstring, $data = null)
    {
        $sql    = "UPDATE {$this->table} SET cronstring=:cronstring, %data% nextExecTime=:nextExecTime WHERE jobid=:jobid";
        $params = [
            'jobid'        => $jobid,
            'cronstring'   => $cronstring,
            'nextExecTime' => ($cronstring == "") ? null : Crontab::getNextTime($cronstring)
        ];

        if ($data) {
            if (is_array($data)) {
                $data = json_encode($data);
            }

            $sql = str_replace("%data%", "data=:data,", $sql);
            $params['data'] = $data;
        } else {
            $sql = str_replace("%data%", "", $sql);
        }

        $this->connection->execute($sql, $params);
        return $this->connection->affectedRows() > 0;
    }

    /**
     * setActive
     *
     * @param int $jobid  jobid
     * @param int $active active
     *
     * @return boolean
     */
    public function setActive($jobid, $active)
    {
        $sql    = "UPDATE {$this->table} SET active=:active WHERE jobid=:jobid";
        $params = [
            'jobid'  => $jobid,
            'active' => $active
        ];

        $this->connection->execute($sql, $params);
        return $this->connection->affectedRows() > 0;
    }

    /**
     * delJob
     *
     * @param int $jobid jobid
     *
     * @return boolean
     */
    public function delJob($jobid)
    {
        $sql = "DELETE FROM {$this->table} WHERE jobid=:jobid";
        $this->connection->execute($sql, ['jobid' => $jobid]);
        return $this->connection->affectedRows() > 0;
    }

    /**
     * 設為立即執行
     *
     * @param int $jobid jobid
     *
     * @return boolean
     */
    public function setInstant($jobid)
    {
        $sql = "UPDATE {$this->table} SET status=:instant WHERE jobid=:jobid AND status!=:running";
        $this->connection->execute($sql, ['jobid' => $jobid, 'instant' => self::STATUS_INSTANT, 'running' => self::STATUS_RUNNING]);
        return $this->connection->affectedRows() > 0;
    }

    /**
     * getJob
     *
     * @param int $jobid jobid
     *
     * @return array
     */
    public function getJob($jobid)
    {
        $sql = "SELECT * FROM {$this->table} WHERE jobid=:jobid";
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['jobid' => $jobid]);
    }

    /**
     * getJobsByProject
     *
     * @param string $project project
     * @param int    $status  status
     *
     * @return array
     */
    public function getJobsByProject($project, $status = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE project=:project";
        $param['project'] = $project;
        if ($status) {
            $sql .= " AND status=:status";
            $param['status'] = $status;
        }
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $param);
    }

    /**
     * searchTask
     *
     * @param array  $list   list
     * @param string $task   task
     * @param array  $config config
     * @param string $type   type
     *
     * @return array
     */
    private function searchTask($list, $task, $config, $type = '')
    {
        foreach ($list as $job) {
            if ($task != $job['task']) {
                continue;
            }

            if ($type && $type != $job['type']) {
                continue;
            }

            $job['desc'] = $config['desc'];
            $job['data'] = json_decode($job['data'], 1);
            return $job;
        }

        $config['task']   = $task;
        $config['status'] = self::STATUS_WAITING;
        $config['active'] = self::INACTIVE;
        $config['type'] = $type;
        $config['lastExecTime'] = '';
        $config['nextExecTime'] = '';
        return $config;
    }

    /**
     * getJobsByProject
     *
     * @param mixed $project project
     *
     * @return array
     */
    public function listJobs($project)
    {
        $list    = $this->getJobsByProject($project);
        $default = $this->listDefaultJobs();
        $result  = [];

        // 可以 import 的 INDEX TYPE
        $projectObj = new Project();
        $import = $projectObj->getImport($project);

        // 建立 job 的清單
        foreach ($default as $task => $config) {
            if ($task == 'importData') {
                foreach ($import as $type => $source) {
                    unset($source);
                    $result[] = $this->searchTask($list, $task, $config, $type);
                }
                continue;
            }

            $result[] = $this->searchTask($list, $task, $config);
        }

        return $result;
    }

    /**
     * getAllJobs
     *
     * @return array
     */
    public function getAllJobs()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
    }

    /**
     * getInstantJobs
     *
     * @return array
     */
    private function getInstantJobs()
    {
        $this->checkConnection();
        $sql = "SELECT * FROM {$this->table} WHERE status=:status";
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['status' => self::STATUS_INSTANT]);
    }

    /**
     * getReadyJobs
     *
     * @return array
     */
    private function getReadyJobs()
    {
        $this->checkConnection();
        $sql = "SELECT * FROM {$this->table} WHERE active=1 AND status=:status AND nextExecTime <= CURRENT_TIMESTAMP()";
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['status' => self::STATUS_WAITING]);
    }

    /**
     * getFailedJobs
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function getFailedJobs()
    {
        $this->checkConnection();
        $sql = "SELECT * FROM {$this->table} WHERE active=1 AND status=:status AND nextExecTime <= CURRENT_TIMESTAMP()";
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ['status' => self::STATUS_FAILED]);
    }

    /**
     * takeJob
     *
     * @param int $jobid jobid
     *
     * @return array
     */
    public function takeJob($jobid)
    {
        $sql = "UPDATE {$this->table} SET lastExecTime=CURRENT_TIMESTAMP(), status=:status WHERE jobid=:jobid";
        $this->connection->execute($sql, ['jobid' => $jobid, 'status' => self::STATUS_RUNNING]);
        return $this->connection->affectedRows() > 0;
    }

    /**
     * finishJob
     *
     * @param int    $jobid        jobid
     * @param string $nextExecTime nextExecTime
     * @param int    $status       status
     *
     * @return boolean
     */
    public function finishJob($jobid, $nextExecTime, $status)
    {
        $sql = "UPDATE {$this->table} SET nextExecTime=:nextExecTime, status=:status WHERE jobid=:jobid";
        $this->connection->execute($sql, ['jobid' => $jobid, 'nextExecTime' => $nextExecTime, 'status' => $status]);
        return $this->connection->affectedRows() > 0;
    }

    /**
     * paramReplace
     *
     * @param array  $parameter parameter
     * @param string $project   project
     * @param string $type      type
     *
     * @return array
     */
    private function paramReplace($parameter, $project, $type)
    {
        foreach ($parameter as $key => $param) {
            switch ($param) {
                case ":PROJECT:":
                    $parameter[$key] = $project;
                    continue;
                case ":TYPE:":
                    $parameter[$key] = $type;
                    continue;
            }
        }
        return $parameter;
    }

    /**
     * run
     *
     * @param int $job job
     *
     * @return void
     */
    private function run($job)
    {
        \GoSearch\Helper\Message::showMessage(date('Y-m-d H:i:s') . "\n");
        \GoSearch\Helper\Message::showMessage("[Cronjob] take job {$job['jobid']} {$job['task']} {$job['project']} {$job['type']}\n");

        // child 各自重新 init connection 以解決 connection 中斷及混亂的問題
        $cronjob = new Cronjob();
        $cronjob->takeJob($job['jobid']);
        $data = json_decode($job['data'], 1);

        if (isset($data['task']) && isset($data['action']) && isset($data['parameter'])) {
            $parameter = $this->paramReplace($data['parameter'], $job['project'], $job['type']);
            \GoSearch\Helper\Task::callTask($data['task'], $data['action'], $parameter);
        }

        if ($job['cronstring'] != '') {
            $nextExecTime = Crontab::getNextTime($job['cronstring']);
            $status = self::STATUS_WAITING;
        } else {
            $nextExecTime = null;
            $status = self::STATUS_STOPPED;
        }

        $cronjob->finishJob($job['jobid'], $nextExecTime, $status);
        \GoSearch\Helper\Message::showMessage("[Cronjob] finish job {$job['jobid']} {$job['task']} {$job['project']} {$job['type']}\n");
        $cronjob->connection->close();
    }

    /**
     * isRunning
     * TODO 未來改成以 http 方式 ping server
     *
     * @return boolean
     */
    private function isRunning()
    {
        self::$lock = fopen($this->pidFile, "c+");
        $gotLock = flock(self::$lock, LOCK_EX | LOCK_NB, $wouldBlock);

        if (!$gotLock && $wouldBlock) {
            return true;
        }

        return false;
    }

    /**
     * initSignal
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function initSignal()
    {
        pcntl_signal(
            SIGTERM,
            function () {
                $this->eventloop->cancelTimer($this->timer);
                \GoSearch\Helper\Message::showMessage("[Cronjob] " . date("Y-m-d H:i:s") . " server is stopped");
                exit(0);
            }
        );

        // 已由 /status 取代，保留但不再使用
        pcntl_signal(
            SIGUSR1,
            function () {
                \GoSearch\Helper\Message::showMessage("[Cronjob] " . date("Y-m-d H:i:s") . "{$this->startTime} {$this->jobcount}");
                file_put_contents($this->statusFile, "{$this->startTime} {$this->jobcount}");
            }
        );
    }

    /**
     * prepareRunning
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function prepare()
    {
        $this->startTime = time();

        // store pid
        ftruncate(self::$lock, 0);
        fputs(self::$lock, getmypid() . "\n");
        fflush(self::$lock);

        // 準備 server 需要的 routing
        $this->routing = [
            '/kill'   => function ($queryString) {
                    parse_str($queryString, $output);
                if (isset($output['jobid'])) {
                    if (isset($this->jobBoard[$output['jobid']])) {
                        $pid = $this->jobBoard[$output['jobid']];
                        posix_kill((int) $pid, SIGTERM);
                        \GoSearch\Helper\Message::showMessage("[Cronjob] kill child $pid");
                        return "Sending SIGTERM to child";
                    }
                }
                    return "job is not exist";
            },
            '/stop'   => function () {
                    // server 送出訊息後，無法再執行後續的 function，因此改成不支援
                    return "本功能暫不支援\n";
            },
            '/status' => function () {
                    \GoSearch\Helper\Message::showMessage("[Cronjob] " . date("Y-m-d H:i:s") . "{$this->startTime} {$this->jobcount}");
                    $now = date('Y-m-d H:i:s');
                    $date = date('Y-m-d H:i:s', $this->startTime);
                    $datetime1 = date_create($now);
                    $datetime2 = date_create($date);
                    $interval = date_diff($datetime2, $datetime1);
                    return $interval->format("系統已啟用 %a 天 %h 小時 %i 分, 共執行 {$this->jobcount} 件工作\n");
                    // file_put_contents($this->statusFile, "{$this->startTime} {$this->jobcount}");
            }
        ];

        // signal trigger
        $this->initSignal();
    }

    /**
     * Get Server Status
     *
     * @return void
     */
    public function getServerStatus()
    {
        if (!$this->isRunning()) {
            return false;
        }

        return file_get_contents("http://localhost:{$this->port}/status");
    }

    /**
     * Kill job
     *
     * @param int $jobid job ID
     *
     * @return void
     */
    public function killJob($jobid)
    {
        if (!$this->isRunning()) {
            return false;
        }

        return file_get_contents("http://localhost:{$this->port}/kill?jobid=$jobid");
    }

    /**
     * getLogFilename
     *
     * @param int $jobid job ID
     *
     * @return string
     */
    private function getLogFilename($jobid)
    {
        return $this->logDir . "/job-{$jobid}.log";
    }

    /**
     * getLogContent
     *
     * @param int $jobid  job ID
     * @param int $offset offset
     *
     * @return string
     */
    public function getLogContent($jobid, $offset = 0)
    {
        return @file_get_contents($this->getLogFilename($jobid), false, null, $offset);
    }

    /**
     * executeJobs
     *
     * @param array $jobs jobs
     *
     * @return void
     */
    private function executeJobs($jobs)
    {
        foreach ($jobs as $job) {
            $this->jobcount++;
            $job['logFile'] = $this->getLogFilename($job['jobid']);
            $childPid = Daemon::executeInParallel(
                function () use ($job) {
                    $this->run($job);
                },
                $job,
                min(count($jobs), self::MAX_PARALLEL_JOB)
            );
            $this->jobBoard[$job['jobid']] = $childPid;
        }
    }

    /**
     * start
     *
     * @return void
     */
    public function start()
    {
        $period = 5;

        if ($this->isRunning()) {
            // \GoSearch\Helper\Message::showMessage("[Cronjob] server is already running");
            return false;
        }
        $this->prepare();

        \GoSearch\Helper\Message::showMessage("[Cronjob] " . date("Y-m-d H:i:s") . " server is started");

        $this->eventloop = \React\EventLoop\Factory::create();
        HttpServer::init($this->eventloop, $this->routing, $this->port);

        // 每 5 秒鐘監聽 signal,
        $this->eventloop->addPeriodicTimer(
            $period,
            function ($timer) use ($period) {
                $this->timer = $timer;
                pcntl_signal_dispatch();

                // 每 5 秒執行 instantjob
                $jobs = $this->getInstantJobs();
                $this->executeJobs($jobs);

                // 每 30秒 執行 cronjob
                if (time() % 30 < $period) {
                    $jobs = $this->getReadyJobs();
                    $this->executeJobs($jobs);
                }

                // 重作失敗的 job
                // if (time() % 60 == 0) {
                // $jobs = $this->getFailedJobs();
                // $this->executeJobs($jobs);
                // }
            }
        );

        $this->eventloop->run();
    }

    /**
     * stop
     *
     * @return void
     */
    public function stop()
    {
        if (!$this->isRunning()) {
            // \GoSearch\Helper\Message::showMessage("[Cronjob] server is not running");
            return;
        }

        $pid = file_get_contents($this->pidFile);
        posix_kill((int) $pid, SIGTERM);
    }
}
