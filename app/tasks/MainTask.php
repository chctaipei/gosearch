<?php
namespace GoSearch\Task;

use Phalcon\Http\Response;

/**
 * MainTask
 *
 * @see https://docs.phalconphp.com/hr/3.3/application-cli
 *
 * @author("hc_chien <hc_chien>")
 */
class MainTask extends \Phalcon\CLI\Task
{
    protected $responseFlag = true;

    /**
     * onConstruct
     *
     * @return void
     */
    public function onConstruct()
    {
    }

    /**
     * get task dir
     *
     * @return string
     */
    private function getDir()
    {
        global $autoloader;
        if (defined('TASK_NAMESPACE') && $autoloader) {
            $prefix = $autoloader->getPrefixesPsr4();
            if (isset($prefix[TASK_NAMESPACE][0])) {
                return $prefix[TASK_NAMESPACE][0];
            }
        }

        if (!defined("PATH_APP")) {
            return __DIR__;
        }

        if (is_dir(PATH_APP . "/tasks")) {
            return PATH_APP . "/tasks";
        } elseif (is_dir(PATH_APP . "/task")) {
            return PATH_APP . "/task";
        }
    }

    /**
     * get all task name
     *
     * @param string $dir task dir
     *
     * @return array
     */
    private function getTaskNames($dir = null)
    {
        if ($dir == null) {
            $dir = $this->getDir();
        }

        if (!$dir) {
            return [];
        }

        $ret = [];
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (preg_match("/(?P<name>^\w+Task)\.php$/", $entry, $matches)) {
                    $ret[] = $matches['name'];
                }
            }
            closedir($handle);
        }

        return $ret;
    }

    /**
     * get Annotations tags
     *
     * @param object $annotations annotations
     *
     * @return array
     */
    private function getAnnotationTags($annotations)
    {
        $tag = [];
        // Traverse the annotations
        foreach ($annotations as $annotation) {
            $name = $annotation->getName();
            $arg = $annotation->getArguments();
            if ($name == 'Route' || $name == 'Get') {
                $tag['Route']['url'] = $arg[0];
                if (isset($arg['methods'])) {
                    $tag['Route']['methods'] = $arg['methods'];
                } elseif ($name == 'Get') {
                    $tag['Route']['methods'] = ['GET'];
                } else {
                    $tag['Route']['methods'] = ['undefined'];
                }
            } else {
                $tag[$name] = $arg[0];
            }
        }

        return $tag;
    }

    /**
     * printCommands
     *
     * @param string $name name
     * @param array  $tag  tag
     *
     * @return boolean
     */
    private function printCommands($name, $tag)
    {
        if (!isset($tag['subject'])) {
            return ;
        }

        isset($tag['arg']) && ($name = "$name {$tag['arg']}");

        $tab = "\t";
        $left = 28 - strlen($name);
        while ($left > 0) {
            $tab .= "\t";
            $left -= 8;
        }

        echo "    $name$tab{$tag['subject']}";
        echo isset($tag['cron']) ? " - {$tag['cron']}" : '';
        if (isset($tag['Route']) || isset($tag['Get'])) {
            // echo "\n\tRoute = ";
            echo "\n\t[" . implode(",", $tag['Route']['methods']) . "] ";
            echo $tag['Route']['url'] ?? '';
        }
        echo "\n";
    }

    /**
     * show All Modules
     *
     * @return void
     */
    private function printModules()
    {
        global $autoloader;
        if (!$autoloader) {
            return $this->printTasks();
        }

        $prefix = $autoloader->getPrefixesPsr4();
        foreach ($prefix as $key => $value) {
            if (strstr($key, 'Wonder\\Task\\')) {
                $arr = explode("\\", $key);
                echo "{$arr[2]}:\n";
                $this->printTasks($key, $value[0]);
                echo "\n";
            }
        }
    }

    /**
     * showAllTask
     *
     * @param string $namespace namespace
     * @param string $dir       task dir
     *
     * @return void
     */
    private function printTasks($namespace = '', $dir = null)
    {
        $taskNames = $this->getTaskNames($dir);
        if (empty($taskNames)) {
            return;
        }

        sort($taskNames);

        if (defined("PATH_CACHE") && is_dir(PATH_CACHE)) {
            $reader = new \Phalcon\Annotations\Adapter\Files(["annotationsDir" => PATH_CACHE]);
        } else {
            $reader = new \Phalcon\Annotations\Adapter\Memory();
        }

        if ($namespace == '' && defined('TASK_NAMESPACE')) {
            $namespace = TASK_NAMESPACE;
        }

        foreach ($taskNames as $name) {
            $reflector = $reader->get($namespace . $name);
            $annotations = $reflector->getClassAnnotations();
            if (!is_object($annotations)) {
                continue;
            }

            $tag = $this->getAnnotationTags($annotations);
            $name = substr($name, 0, -4);
            $this->printCommands($name, $tag);
        }
    }

    /**
     * printUsage
     *
     * @return void
     */
    private function printUsage()
    {
        if (defined("PATH_CACHE") && is_dir(PATH_CACHE)) {
            $reader = new \Phalcon\Annotations\Adapter\Files(["annotationsDir" => PATH_CACHE]);
        } else {
            $reader = new \Phalcon\Annotations\Adapter\Memory();
        }

        $reflector = $reader->get(get_class($this));

        $annotations = $reflector->getClassAnnotations();
        if ($annotations->has('subject')) {
            $tag = $annotations->get('subject');
            echo implode("", $tag->getArguments()), ":\n";
        }

        $methodAnnotations = $reflector->getMethodsAnnotations();

        // echo "Commands:\n";
        foreach ($methodAnnotations as $name => $methodAnnotation) {
            $annotations = $methodAnnotation->getAnnotations();
            $tag = $this->getAnnotationTags($annotations);
            $name = substr($name, 0, -6);
            $this->printCommands($name, $tag);
        }
    }

    /**
     * 列出功能表
     *
     * @return void
     */
    public function listAllAction()
    {
        if ('GoSearch\\Task\\MainTask' != get_class($this)) {
            return;
        }
    }

    /**
     * 列出功能表
     *
     * @return void
     *
     * @subject("列出功能表")
     *
     * @Get("/help")
     */
    public function helpAction()
    {
        if (PHP_SAPI != 'cli') {
            echo "<pre>";
        }

        if ('GoSearch\\Task\\MainTask' == get_class($this)) {
            echo "操作指令:\n";
            // return $this->printTasks();
            return $this->printModules();
        }

        $this->printUsage();
    }

    /**
     * main
     *
     * @return void
     */
    public function mainAction()
    {
        return $this->helpAction();
    }
    /**
     * stringifyMessage
     *
     * @param mixed $message message
     *
     * @return string
     */
    private function stringifyMessage($message)
    {
        if (!is_string($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return $message;
    }

    /**
     * Show message
     *
     * @param string $message debug message
     *
     * @return void
     */
    public function showMessage($message)
    {
        if (PHP_SAPI === 'cli') {
            $str = $this->stringifyMessage($message) . "\n";
            if (isset($GLOBALS['_LOGFILE'])) {
                file_put_contents($GLOBALS['_LOGFILE'], $str, FILE_APPEND);
            } else {
                echo $str;
            }
        }
    }

    /**
     * fixParams
     *
     * @param array $args args
     *
     * @return array
     */
    protected function fixParams($args)
    {
        if (is_array($args[0])) {
            return $args[0];
        }
        return $args;
    }

    /**
     * validateParams
     *
     * @param array   $params params
     * @param integer $count  count
     *
     * @return boolean
     */
    public function validateParams($params, $count)
    {
        // for MVC
        //if ($this->getDI()->has('request')) {
        //    return true;
        // }

        if (count($params) < $count) {
            $this->showMessage("缺少參數");
            return false;
        }

        return true;
    }

    /**
     * fetch from param
     *
     * @param string  $name   檔名 或 json string
     * @param boolean $decode json decode
     *
     * @return array or string
     */
    public function fetchParam($name, $decode = true)
    {
        if (is_array($name)) {
            return $name;
        }

        // for MVC
        // if ($this->getDI()->has('request')) {
        //     return $this->request->getJsonRawBody(true);
        // }

        if ($name == '') {
            // debugLog("內容為空\n");
            return null;
        }

        if (PHP_SAPI === 'cli') {
            $file = realpath($name);
            if ($file) {
                $schema = file_get_contents($file);
                if ($decode) {
                    $schema = json_decode($schema, 1);
                    if (is_null($schema)) {
                        // debugLog("[fetchParam] $file 非 json 格式或格式錯誤\n");
                        return null;
                    }
                }
                return $schema;
            }
        }

        if (!$decode) {
            return $name;
        }

        $ret = json_decode($name, 1);
        if (is_array($ret)) {
            return $ret;
        }

        return null;
    }

    /**
     * response
     *
     * @param array $data data
     *
     * @return void
     */
    public function printResult($data)
    {
        if (is_string($data)) {
            echo $data, "\n";
        } elseif (is_array($data)) {
            $ret = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($ret == false) {
                echo json_last_error_msg(), "\n";
            } else {
                echo $ret;
            }
        }
    }

    /**
     * showJsonResupt
     *
     * @param string $status status
     * @param mix    $data   data
     *
     * @return void
     */
    public function showJsonResult($status, $data)
    {
        $ret['status'] = $status;

        if (is_string($data)) {
            $ret['message'] = $data;
        } elseif (is_array($data)) {
            $ret = array_merge($ret, $data);
        }

        $response = new Response();

        if (isset($ret['status'])) {
            $response->setStatusCode($ret['status'], "");
        }

        $response->setContentType('application/json', 'UTF-8');
        $response->setContent(json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $response->send();
    }

    /**
     * setResponse
     *
     * @param boolean $flag boolean
     *
     * @return $this
     */
    public function setResponse($flag = true)
    {
        $this->responseFlag = $flag;
        return $this;
    }

    /**
     * response
     *
     * @param string $status status
     * @param mix    $data   data
     *
     * @return void
     */
    public function response($status, $data = null)
    {
        if ($this->responseFlag == false) {
            $ret['status'] = $status;

            if (is_string($data)) {
                $ret['message'] = $data;
            } elseif (is_array($data)) {
                $ret = array_merge($ret, $data);
            }

            return $ret;
        }

        if (PHP_SAPI === 'cli') {
            return $this->printResult($data);
        }

        return $this->showJsonResult($status, $data);
    }
}
