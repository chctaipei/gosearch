<?php
namespace GoSearch;

use PDO;

/**
 * Load Data from Database
 *
 * @author hc_chien <hc_chien@hiiir.com>
 */
class Importer
{
    private $stmt;
    private $importFilter;

    /**
     * __construct
     *
     * @param array $config config
     *
     * @return void
     */
    public function __construct($config)
    {
        $dsn = $config['dsn'];
        $username = $config['username'];
        $password = $config['password'];
        $sql = $config['sql'];

        if (isset($config['filter']) && $config['filter']) {
            $this->setImportFilter($config['filter']);
        }

        $dbh = new PDO($dsn, $username, $password);
        // $param = array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) ??
        $this->stmt = $dbh->prepare($sql);
        $this->stmt->execute();
    }

    /**
     * filter
     *
     * @param array $filter filter
     *
     * @return void
     */
    public function setImportFilter($filter)
    {
        $className = "GoSearch\\Plugin\\Filter\\" . $filter;
        $this->importFilter = new $className();
    }

    /**
     * filter
     *
     * @param mixed $data data
     *
     * @return array
     */
    public function filter($data)
    {
        if (!$this->importFilter) {
            return $data;
        }
        return $this->importFilter->filter($data);
    }

    /**
     * read
     *
     * @return array
     */
    public function read()
    {
        $count = 0;
        $arr   = [];

        while ($row = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $arr[] = $this->filter($row);
            if (++$count >= 1000) {
                yield($arr);
                $count = 0;
                $arr   = [];
            }
        }

        if ($count) {
            yield($arr);
        }
    }
}
