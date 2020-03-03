<?php
namespace GoSearch\Helper;

/**
 * Message Helper
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Task
{

    /**
     * callTask
     *
     * @param string $task      task
     * @param string $action    action
     * @param array  $parameter parameter
     *
     * @return mixed
     */
    public static function callTask($task, $action, $parameter = [])
    {
        $className = $task;
        if (!strpos($task, "Task", -4)) {
            $className = "{$task}Task";
        }

        $className = "\\GoSearch\\Task\\$className";
        $class = new $className;
        $method = "{$action}Action";
        return $class->setResponse(false)->$method($parameter);
    }
}
