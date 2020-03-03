<?php
namespace GoSearch;

/**
 * Worker Daemon
 */
class Daemon
{
    private static $childs = 0;

    /**
     * 關閉 STDIN,STDOUT,STDERR
     *
     * @param array $param parameters
     *
     * @return void
     */
    private static function closeStdio($param)
    {
        global $STDIN, $STDOUT, $STDERR;

        fflush(STDOUT);
        fflush(STDERR);

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        $GLOBALS['_LOGFILE'] = $param['logFile'] ?? "/dev/null";
        // truncate file
        file_put_contents($GLOBALS['_LOGFILE'], '');

        $STDIN  = fopen("/dev/null", 'r');
        $STDOUT = fopen("/dev/null", 'w');
        $STDERR = fopen("/dev/null", 'w');
        // $STDOUT = fopen($GLOBALS['_LOGFILE'], 'w');
        // $baseDir = dirname(__FILE__);
        // ini_set('error_log', $baseDir.'/error.log');
        // $STDERR = fopen($file, 'a');
        // $STDERR = $STDOUT;
    }

    /**
     * 產生 workers
     *
     * Example:
     * WorkerDaemon::executeInParallel(
     * function () { sleep(3); echo "hello\n"; * },
     * [],
     * 10
     * );
     *
     * @param string  $func      function name
     * @param array   $param     parameters
     * @param integer $maxChilds fork childs
     *
     * @return int childPid
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public static function executeInParallel($func, $param, $maxChilds = 10)
    {
        while (self::$childs >= $maxChilds) {
            $endPid = pcntl_waitpid(-1, $status, WUNTRACED);
            if ($endPid < 0) {
                // error
                \GoSearch\Helper\Message::debugLog("[executeInParallel] pcntl_waitpid failed, status: $status");
                exit;
            } elseif (0 == $endPid) {
                // no child exit
                \GoSearch\Helper\Message::debugLog("[executeInParallel] no child exit");
                continue;
            } else {
                // child exits
                --self::$childs;
                // echo "child process {$endPid} exits\n";
            }
        }

        $childPid = pcntl_fork();
        if (0 == $childPid) {
            $mypid = getmypid();
            self::closeStdio($param);

            try {
                call_user_func($func, $param);
            } catch (\Exception $e) {
                // exception
                \GoSearch\Helper\Message::exceptionLog($e);
            }

            // 直接 exit 會讓 db connection 中斷
            // 由 command line 啟動的 daemon 不會有影響
            // 9 = SIGKILL
            posix_kill($mypid, 9);
            pcntl_signal_dispatch();

            // 直接 exit 會讓 db connection 中斷
            // 由 command line 啟動的 daemon 不會有影響
            exit;
        }//end if

        self::$childs++;
        return $childPid;
    }
}
?>
