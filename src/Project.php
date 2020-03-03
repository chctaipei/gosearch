<?php
namespace GoSearch;

/**
 * 專案資料庫
 **/
class Project extends DBFactory
{
    protected $table      = "project";
    protected $schemaFile = __DIR__ . "/../config/db/project.sql";
    protected $project;

    /**
     * __construct
     *
     * @param string $project project
     *
     * @return void
     */
    public function __construct($project = null)
    {
        parent::__construct();

        if ($project) {
            $this->select($project);
        }
    }

    /**
     * select project
     *
     * @param string $project project
     *
     * @return this
     */
    public function select($project)
    {
        if (!ctype_alnum($project)) {
            throw new \Exception("專案名稱只允許使用英數字");
        }

        $this->project = strtolower($project);
        return $this;
    }

    /**
     * getProjects
     *
     * @param int $offset offset
     * @param int $count  count
     *
     * @return array
     */
    public function getProjects($offset = 0, $count = 10)
    {
        $offset = (int) $offset;
        $count  = (int) $count;

        $sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$this->table} ORDER BY name ASC LIMIT $offset, $count";
        $ret = $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);

        foreach ($ret as $id => $hits) {
            $ret[$id]['data'] = json_decode($hits['data'], 1);
        }
        $result['hits'] = $ret;

        $sql = "SELECT FOUND_ROWS() as total";
        $ret = $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
        $result['total'] = $ret[0]['total'];

        return $result;
    }

    /**
     * getImport
     *
     * @param mixed $project project
     *
     * @return array
     */
    public function getImport($project)
    {
        if (!$project) {
            $project = $this->project;
        }

        $ret = $this->getProject($project);
        if (isset($ret['import'])) {
            return $ret['import'];
        }
        return [];
    }

    /**
     * getProject
     *
     * @param string $project project
     * @param string $column  column
     *
     * @return array
     */
    public function getProject($project = null, $column = "")
    {
        if (!$project) {
            $project = $this->project;
        }

        if ($column) {
            $column = "JSON_EXTRACT(data, '\$.{$column}')";
        } else {
            $column = "data";
        }

        $sql = "SELECT $column as data FROM {$this->table} WHERE name = :project";
        $ret = $this->connection->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, ['project' => $project]);

        if (isset($ret['data'])) {
            return json_decode($ret['data'], 1);
        }

        return null;
    }

    /**
     * updateProject
     *
     * @param string $project project
     * @param array  $data    data
     *
     * @return boolean
     */
    public function updateProject($project, $data)
    {
        if (!ctype_alnum($project)) {
            throw new \Exception("專案名稱只允許使用英數字");
        }

        $project = strtolower($project);
        $json    = json_encode($data, JSON_UNESCAPED_UNICODE);

        $this->connection->execute(
            "INSERT INTO {$this->table} (name, data) VALUES (:project, :data1) ON DUPLICATE KEY UPDATE data = :data2",
            [
                'project' => $project,
                'data1'   => $json,
                'data2'   => $json
            ]
        );

        // \GoSearch\Helper\Message::debugLog("[updateProject] $project $json");
        return $this->connection->affectedRows() > 0;
    }

    /**
     * deleteProject
     *
     * @param string $project project
     *
     * @return boolean
     */
    public function deleteProject($project = null)
    {
        if (!$project) {
            $project = $this->project;
        }

        $this->connection->execute(
            "DELETE FROM {$this->table} WHERE name = :project",
            ['project' => $project]
        );

        \GoSearch\Helper\Message::debugLog("[deleteProject] $project");
        return $this->connection->affectedRows() > 0;
    }

    /**
     * validate Tag (英數字和底線)
     *
     * @param string $tag tag
     *
     * @return boolean
     */
    private function validateTag($tag)
    {
        return preg_match("/^[\w\d_]+$/", $tag);
    }

    /**
     * setProjectData
     *
     * @param string $project project
     * @param string $name    name
     * @param string $tag     tag
     * @param mixed  $value   value
     *
     * @return boolean
     */
    public function setProjectData($project, $name, $tag, $value)
    {
        $name = strtolower($name);
        $data = $this->getProject($project);

        if ($data === null) {
            throw new \Exception("$project 專案不存在");
        }

        if ($tag == ".") {
            $data[$name] = $value;
        } elseif (empty($value)) {
            unset($data[$name][$tag]);
        } else {
            if (!$this->validateTag($tag)) {
                throw new \Exception("[$tag] 不合法, 只能使用英數字和底線");
            }

            $data[$name][$tag] = $value;
        }

        return $this->updateProject($project, $data);
    }

    /**
     * setData
     *
     * @param string $name  name
     * @param string $tag   tag
     * @param mixed  $value value
     *
     * @return boolean
     */
    public function setData($name, $tag, $value)
    {
        if (!$this->project) {
            throw new \Exception("未指定 project");
        }

        return $this->setProjectData($this->project, $name, $tag, $value);
    }

    /**
     * getData
     *
     * @param string $name name
     *
     * @return array
     */
    public function getData($name)
    {
        if (!$this->project) {
            throw new \Exception("未指定 project");
        }
        return $this->getProject($this->project, $name);
    }

    /**
     * setBackup 設定輪替 (0=取消)
     *
     * @param string $type  type
     * @param int    $value value
     *
     * @return boolean
     */
    public function setBackup($type, $value)
    {
        if (!$this->project) {
            throw new \Exception("未指定 project");
        }

        if ($value == 0) {
            // 取消
            $ret = null;
        } else {
            $ret['count'] = $value;
            for ($i = 1; $i <= $value + 1; $i++) {
                $ret[$i] = null;
            }
        }

        // \GoSearch\Helper\Message::debugLog("[setBackup] type = $type, count = $value");
        return $this->setProjectData($this->project, 'backup', $type, $ret);
    }

    /**
     * updateBackupTime
     *
     * @param string $type type
     * @param int    $tid  tid
     *
     * @return boolean
     */
    public function updateBackupTime($type, $tid)
    {
        $backup = $this->getBackup();
        $data = $backup[$type] ?? [];
        $data[$tid] = time();
        \GoSearch\Helper\Message::debugLog("[updateBackupTime] type = $type, tid = $tid");
        return $this->setProjectData($this->project, 'backup', $type, $data);
    }

    /**
     * __call
     *
     * @param string $name      name
     * @param array  $arguments arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $property = substr($name, 3);
        if (!$property) {
            return;
        }

        $property = strtolower($property);

        if (substr($name, 0, 3) == 'get') {
            return $this->getData($property);
        }

        if (substr($name, 0, 3) == 'set') {
            if (count($arguments) == 2) {
                return $this->setData($property, $arguments[0], $arguments[1]);
            } else {
                return $this->setData($property, ".", $arguments[0]);
            }
        }
    }
}
