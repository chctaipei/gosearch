<?php
namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;
use GoSearch\Constant;
use GoSearch\QueryLog;
use GoSearch\Badword;
use GoSearch\User;
use GoSearch\Cronjob;

/**
 * 搜尋引擎設定
 *
 * @author hc_chien <hc_chien>
 */
class Admin extends Index
{
    protected $projectPath = __DIR__ . "/../config/project";

    /**
     * __construct
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function __construct()
    {
    }

    /**
     * 初始化, 建立 config 的 index
     *
     * @return void
     */
    public function initSystem()
    {
        $user = new User();
        $user->createTable();
        $project = new Project();
        $project->createTable();
        $cronjob = new Cronjob();
        $cronjob->createTable();
        // $badWord = new Badword();
        // $badWord->createTable();
    }

    /**
     * initQueryLog
     *
     * @param string $project project
     *
     * @return void
     */
    private function initQueryLog($project)
    {
        $queryLog = new QueryLog($project);

        if ($queryLog->createTable()) {
            $queryLog->initTable();
        }
    }

    /**
     * createBadword
     *
     * @param string $project project
     *
     * @return void
     */
    private function createBadword($project)
    {
        $badword = new Badword($project);
        $badword->createTable();
    }

    /**
     * createBoost
     *
     * @param string $project project
     *
     * @return void
     */
    private function createBoost($project)
    {
        $badword = new Boost($project);
        $badword->createTable();
    }

    /**
     * processPath
     *
     * @param string $path     dir
     * @param string $pattern  file pattern
     * @param func   $callback callback
     *
     * @return void
     */
    private function processPath($path, $pattern, $callback)
    {
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if (preg_match($pattern, $entry, $matches)) {
                    // $data = json_decode(file_get_contents("$path/$entry"), 1);
                    $data = file_get_contents("$path/$entry");
                    $callback($matches, $data);
                }
            }
            closedir($handle);
        }
    }

    /**
     * initProjectIndex
     *
     * @param string $project project
     *
     * @return void
     */
    public function initProjectIndex($project)
    {
        $projectObj = new Project($project);
        $path = "{$this->projectPath}/$project/index";
        $this->processPath(
            $path,
            "/(?P<name>^\w+)Index\.json$/",
            function ($matches, $data) use ($projectObj) {
                $data = json_decode($data, 1);
                $projectObj->setIndex($matches['name'], $data);
            }
        );

        // 建立
        $indexHandler = new ProjectIndex($project);
        return $indexHandler->create(ProjectIndex::ALL_INDEX_TYPE);
    }

    /**
     * initProjectIndex
     *
     * @param string $project project
     * putSchema "name" {json:  ui:  type:  output:  }
     * putScript "name" { }
     *
     * @return void
     */
    public function initProjectSearch($project)
    {
        $projectSearch = new ProjectSearch($project);
        $path = "{$this->projectPath}/$project/search";
        $this->processPath(
            $path,
            "/(?P<name>^\w+)Search\.script$/",
            function ($matches, $data) use ($project, $projectSearch) {
                $name = $matches['name'];
                $projectSearch->putScript($name, trim($data));

                $file = "{$this->projectPath}/$project/search/{$name}Search.json";
                if (file_exists($file)) {
                    $schema['json'] = json_decode(file_get_contents($file), 1);
                }
                $file = "{$this->projectPath}/$project/search/{$name}SearchUI.json";
                if (file_exists($file)) {
                    $schema['ui'] = json_decode(file_get_contents($file), 1);
                }
                $file = "{$this->projectPath}/$project/search/{$name}SearchOutput.script";
                if (file_exists($file)) {
                    // 要把 { 替換掉
                    $schema['output'] = str_replace("{", "&#123;", trim(file_get_contents($file)));
                }
                $file = "{$this->projectPath}/$project/search/{$name}Search.type";
                if (file_exists($file)) {
                    $schema['type'] = trim(file_get_contents($file));
                }
                if (isset($schema)) {
                    $projectSearch->putScript($name.".schema", $schema);
                }
            }
        );
    }

    /**
     * 初始化預設的專案
     * 順序:
     * AdminTask
     * - initProject PROJECT FILE            根據 config/project/project.yml 對專案做初始化建置
     * - setIndex    PROJECT TYPE FILE       新增 index type mapping
     * - setSource   PROJECT SOURCENAME FILE 新增資料來源
     * - setBackup   PROJECT TYPE BACKUP (OPTIONAL) 設定 index 的備分數
     * IndexTask
     * - create      PROJECT [TYPE]          建立索引 ('all' 表示全部)
     * AdminTask
     * - setImport   PROJECT TYPE SOURCENAME 設定 index 與資料來源的對應關係
     * SearchTask
     * - putScript   PROJECT SCRIPTID SOURCE 設定搜尋樣板
     * - putSchema   PROJECT SCRIPTID SOURCE 設定樣板的 JSON/UI SCHEMA
     *
     * @param string $project project project
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function initDefaultProject($project)
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        if ($di && $di->has("config")) {
            $config = $di->get("config");
            $this->projectPath = $config->gosearch->application->projectConfigDir ?? $config->application->projectConfigDir ?? __DIR__ . "/../config/project";
        }

        $projectList = [];
        if ($handle = opendir($this->projectPath)) {
            while (false !== ($entry = readdir($handle))) {
                if (is_dir("{$this->projectPath}/$entry") && $entry[0] != '.') {
                    $projectList[] = $entry;
                }
            }
            closedir($handle);
        }

        $projectObj = new Project();
        $result = [];
        foreach ($projectList as $default) {
            if ($project == null) {
                // 建立 QueryLog table
                $this->initQueryLog($default);
                // 建立 Badword table
                $this->createBadword($default);
                // 建立 Boost table
                $this->createBoost($default);
            }

            if ($project && $default != $project) {
                continue;
            }

            if (file_exists("{$this->projectPath}/$default/config.json")) {
                 $config = json_decode(file_get_contents("{$this->projectPath}/$default/config.json"), 1);
                 $result[$default] = $projectObj->updateProject($default, $config);
            }

            if (is_dir("{$this->projectPath}/$default/index")) {
                $this->initProjectIndex($default);
            }

            if (is_dir("{$this->projectPath}/$default/search")) {
                $this->initProjectSearch($default);
            }

            if ($project) {
                return $result[$project];
            }
        }//end foreach

        if (empty($result) && $project) {
            $data = [
                 'index' => []
             ];
            return $projectObj->updateProject($project, $data);
        }

        return $result;
    }

    /**
     * 初始化專案
     * $data = ['setting' => [...], 'index' => [...], 'search' => [...], 'sync' => 'xxx'];
     *
     * @param string $project project
     * @param array  $data    data
     *
     * @return array
     */
    public function initProject($project = null, $data = null)
    {
        $project = strtolower($project);

        if ($project) {
            // 建立 QueryLog table
            $this->initQueryLog($project);
            // 建立 Badword table
            $this->createBadword($project);
            // 建立 Boost table
            $this->createBoost($project);
        }

        if (!$data) {
            return $this->initDefaultProject($project);
        }

        $projectObj = new Project($project);
        return $projectObj->updateProject($project, $data);
    }

    /**
     * 刪除專案(只有 config 設定), 不會刪除既有的 data 與 index
     *
     * @param string $project 專案
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteProject($project)
    {
        $queryLog = new QueryLog($project);
        $queryLog->deleteTable();

        $projectObj = new Project();
        $projectObj->deleteProject($project);
        return;
    }
}
