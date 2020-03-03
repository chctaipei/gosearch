<?php

namespace GoSearch\Exception;

use Exception;

/**
 * 基礎例外
 *
 * @package    Support
 * @subpackage Exception
 * @filesource
 */
abstract class BaseException extends Exception
{
    protected $configPath    = ".";
    private static $initPath = null;
    private static $includes = [];
    private static $buffer;
    protected $httpCode = 400;
    protected $alertCode;
    protected $debugMessage;
    protected $extMessage;
    private $defaultFile;
    private $exceptions;
    private $group;
    protected $code = "";

    /**
     * 初始化
     *
     * @return void
     */
    private function initialize()
    {
        if (!(null == self::$initPath)) {
            $this->configPath = self::$initPath;
        }

        $class = get_class($this);

        $tmp = substr(strrchr($class, "\\"), 1);
        if ('' != $tmp) {
            $class = $tmp;
        }

        $this->exceptions  = &self::$buffer[$class];
        $this->defaultFile = strtolower(substr($class, 0, -9)).'.yml';
    }

    /**
     * 讀取 yaml config file
     *
     * @param string $fileName 檔名
     *
     * @return array $configAry;
     */
    private function loadYaml($fileName)
    {
        $fileName = realpath($fileName);
        if (isset($this->includes[$fileName])) {
            return [];
        }

        $this->includes[$fileName] = 1;

        return (array) yaml_parse_file($fileName);
    }

    /**
     * 讀取 config file
     *
     * @param string $fileName 檔名
     *
     * @return array $configAry;
     *
     * @throws Exception If file not found
     */
    private function loadFile($fileName = null)
    {
        if (null == $fileName) {
            $fileName = $this->configPath."/".$this->defaultFile;
            if (file_exists($fileName)) {
                return $this->loadYaml($fileName);
            }

            throw new parent(
                "{$this->defaultFile} not found in path ".$this->configPath,
                400
            );
        }

        if (file_exists($fileName)) {
            return $this->loadYaml($fileName);
        }

        if (file_exists($this->configPath."/$fileName")) {
            $fileName = $this->configPath."/$fileName";

            return $this->loadYaml($fileName);
        }

        throw new parent("$fileName not found");
    }

    /**
     * Construct function
     *
     * @param string $message  除錯訊息
     * @param int    $code     error Code 預設 null
     * @param object $previous previous Exception
     * @param int    $httpCode HTTP Status code
     *
     * @return void
     *
     * @throws Exception If $code not found
     */
    public function __construct(
        $message = "",
        $code = null,
        \Exception $previous = null,
        $httpCode = null
    ) {
        $this->initialize();
        $this->mergeConfig();

        // code 不存在
        if (null == $code || !isset($this->exceptions['code'][$code])) {
            // undefined error code
            throw new parent("Exception code:$code is undefined");
        }

        $message = $this->handleMessage($code, $message);
        if (null !== $httpCode) {
            $this->httpCode = $httpCode;
        }

        if (is_array($message)) {
            $message = $message['message'];
        }

        $this->code = $code;
        parent::__construct($message, null, $previous);
    }

    /**
     * 設定 config file 路徑
     *
     * @param string $path 路徑
     *
     * @return void
     */
    public function setConfigPath($path)
    {
        $this->configPath = $path;
    }

    /**
     * 載入 config 檔
     *
     * @param string $fileName 檔名
     *
     * @return void
     */
    public function mergeConfig($fileName = null)
    {
        $data = $this->loadFile($fileName);

        if (is_array($this->exceptions)) {
            $this->exceptions = array_merge($data, $this->exceptions);
        } else {
            $this->exceptions = $data;
        }
    }

    /**
     * 1. 若 groupMessage 存在,
     *    則 groupMessage 為一般訊息, codeMessage 為詳細訊息
     * 2. 若 groupMessage 不存在, 則 codeMessage 為一般訊息
     * (3.) 若 groupMessage, codeMessage 都不存在,
     *    則以 function 輸入訊息為一般訊息
     * PS: codeMessage 一定會存在, (3)不會有作用
     *
     * @param int    $code    error Code
     * @param string $message 訊息
     *
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function handleMessage($code, $message)
    {
        $this->debugMessage = $message;

        if (isset($this->exceptions['group'])) {
            // group:
            foreach ($this->exceptions['group'] as $group => $msg) {
                if (substr($code, 0, strlen($group)) == $group) {
                    $this->group = $group;
                    if (isset($msg['httpCode'])) {
                        $this->httpCode = $msg['httpCode'];
                        $groupMessage   = $msg['message'];
                    } else {
                        $groupMessage = $msg;
                    }

                    if (isset($msg['alertCode'])) {
                        $this->alertCode = $msg['alertCode'];
                    }

                    break;
                }
            }
        }

        if (isset($this->exceptions['code'][$code])) {
            // code:
            $data = $this->exceptions['code'][$code];
            if (isset($data['httpCode'])) {
                $this->httpCode = $data['httpCode'];
                $message        = $data['message'];
            } else {
                $message = $data;
            }

            if (isset($data['alertCode'])) {
                $this->alertCode = $data['alertCode'];
            }
        }

        if (isset($groupMessage)) {
            $this->extMessage = $message;
            $message          = $groupMessage;
        }

        if (is_array($this->extMessage)) {
            unset($this->extMessage['alertCode']);
        }

        return $message;
    }

    /**
     * Get alert code
     *
     * @return extend message
     */
    public function getAlertCode()
    {
        return $this->alertCode;
    }

    /**
     * Set http code
     *
     * @param int $code httpCode
     *
     * @return extend message
     */
    public function setHttpCode($code)
    {
        $this->httpCode = $code;
    }

    /**
     * Get http code
     *
     * @return extend message
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Set extend message
     *
     * @param string $message extend message
     *
     * @return extend message
     */
    public function setExMessage($message)
    {
        $this->extMessage = $message;
    }

    /**
     * Get extend message
     *
     * @return extend message
     */
    public function getExMessage()
    {
        return $this->extMessage;
    }

    /**
     * Get Message group
     *
     * @return group
     */
    public function getMessageGroup()
    {
        return $this->group;
    }
}
