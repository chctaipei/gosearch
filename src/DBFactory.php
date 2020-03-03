<?php
namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;

/**
 * Class: Factory
 *
 * @abstract
 */
abstract class DBFactory
{
    // DB connection
    public $connection = null;

    // must set
    protected $table      = null;
    protected $schemaFile = null;

    /**
     * __construct
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function __construct()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();
        if (!$di) {
            return;
        }

        if ($di->has("generalMaster")) {
            $this->connection = $di->getShared("generalMaster");
        } elseif ($di->has("db")) {
            $this->connection = $di->get("db");
        /*
        } elseif (isset($config)) {
            $this->initDbConnection($config->database);
            $di->set("db", $this->connection);
        */
        }
        $this->connection->execute('use gosearch');

    }

    /**
     * Initialize DB Connection
     *
     * @param \Phalcon\Config $dbConfig db config object
     *
     * @return \Phalcon\Db\Adapter\Pdo\Mysql $connection
     *
     * @codeCoverageIgnore
     */
    public function initDbConnection($dbConfig)
    {
        $this->connection = new \Phalcon\Db\Adapter\Pdo\Mysql($dbConfig);
        return $this->connection;
        /*
            $connectHandler   = new Connection();
            $this->connection = $connectHandler->getDbConnection($dbConfig);
            return $this->connection;
        */
    }

    /**
     * CREATE table
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function createTable()
    {
        // $class = get_called_class();
        $sql = file_get_contents($this->schemaFile);
        $sql = str_replace(":TABLE:", $this->table, $sql);

        \GoSearch\Helper\Message::debugLog("[createTable] $sql");
        $this->connection->execute($sql);

        return $this->connection->affectedRows() > 0;
    }

    /**
     * deleteTable
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteTable()
    {
        $sql = "DROP TABLE IF EXISTS {$this->table}";

        $this->connection->execute($sql);
        \GoSearch\Helper\Message::debugLog("[deleteTable] $sql");

        return $this->connection->affectedRows() > 0;
    }

    /**
     * checkConnection
     *
     * @return void
     */
    protected function checkConnection()
    {
        try {
            $this->connection->fetchAll('SELECT 1');
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                $this->connection->connect();
            }
        }
    }
}
