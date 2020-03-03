<?php
namespace GoSearch\Helper;

/**
 * Message Helper
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Message
{

    /**
     * Set message level
     *
     * @param int $level level
     *
     * @return void
     */
    public static function setMessageLevel($level)
    {
        $GLOBALS['_MSG_LEVEL'] = $level;
    }

    /**
     * stringifyMessage
     *
     * @param mixed $message message
     *
     * @return string
     */
    public static function stringifyMessage($message)
    {
        if (!is_string($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return $message;
    }

    /**
     * Show debug message
     *
     * @param string $message debug message
     * @param array  $context context
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function debugLog($message, $context = [])
    {
        global $di;

        if ($di && $di->has("logger")) {
            $logger = $di->get("logger");
            $message = self::stringifyMessage($message);
            $logger->debug($message, $context);
        }
        // error_log(stringifyMessage($message));
        self::showMessage($message, 0);
    }

    /**
     * Show message
     *
     * @param string $message debug message
     * @param int    $level   message level
     *
     * @return void
     */
    public static function showMessage($message, $level = 1)
    {
        $glevel = $GLOBALS['_MSG_LEVEL'] ?? 1;
        if ($level >= $glevel) {
            if (PHP_SAPI === 'cli') {
                $str = self::stringifyMessage($message) . "\n";
                if (isset($GLOBALS['_LOGFILE'])) {
                    file_put_contents($GLOBALS['_LOGFILE'], $str, FILE_APPEND);
                } else {
                    echo $str;
                }
            }
        }
    }

    /**
     * exceptionLog (未來可以加上 extra 來傳遞內部資訊)
     *
     * @param \Exception $e execption
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function exceptionLog(\Exception $e)
    {
        global $di;

        if ($di && $di->has("logger")) {
            $logger = $di->get("logger");
            $context['file'] = $e->getFile();
            $context['line'] = $e->getLine();
            $message = $e->getMessage();
            $logger->error($e->getMessage(), $context);
        }

        self::showMessage($message, 0);
    }
}
