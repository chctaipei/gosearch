<?php
namespace GoSearch;

use GoSearch\Admin;

/**
 * Class: User
 *
 * @see Factory
 */
class User extends DBFactory
{
    protected $table      = "user";
    protected $schemaFile = __DIR__ . "/../config/db/user.sql";

    /**
     * addUser
     *
     * @param string $account account
     * @param string $name    name
     * @param int    $level   level
     *
     * @return int
     */
    public function addUser($account, $name, $level)
    {
        $this->connection->execute(
            "INSERT IGNORE INTO {$this->table} SET account=:account, name=:name, level=:level, createTime=NOW(), updateTime=NOW()",
            [
                'account' => $account,
                'name'    => $name,
                'level'   => $level
            ]
        );

        // \GoSearch\Helper\Message::debugLog("[addUser] INSERT IGNORE INTO {$this->table} SET account=\"$account\", name=\"$name\", level=$level");
        return $this->connection->affectedRows() > 0;
    }

    /**
     * update
     *
     * @param string $account account
     * @param string $column  column
     * @param string $value   value
     *
     * @return int
     */
    public function update($account, $column, $value)
    {
        if (!in_array($column, ['name', 'level'])) {
            return -1;
        }

        $this->connection->execute(
            "UPDATE {$this->table} SET $column=:value, updateTime=NOW() WHERE account=:account",
            [
                'account' => $account,
                'value'   => $value
            ]
        );

        // \GoSearch\Helper\Message::debugLog("[update] UPDATE {$this->table} SET $column=\"$value\" WHERE account=\"$account\"");
        return $this->connection->affectedRows() > 0;
    }

    /**
     * updateName
     *
     * @param string $account account
     * @param string $name    name
     *
     * @return int
     */
    public function updateName($account, $name)
    {
        return $this->update($account, 'name', $name);
    }

    /**
     * updateLevel
     *
     * @param string $account account
     * @param int    $level   level
     *
     * @return int
     */
    public function updateLevel($account, $level)
    {
        $this->removeSession($account);
        return $this->update($account, 'level', $level);
    }

    /**
     * delUser
     *
     * @param string $account account
     *
     * @return int
     */
    public function delUser($account)
    {
        $this->removeSession($account);
        $this->connection->execute(
            "DELETE FROM {$this->table} WHERE account=:account",
            [
                'account' => $account
            ]
        );

        // \GoSearch\Helper\Message::debugLog("[delUser] DELETE FROM {$this->table} WHERE account=\"$account\"");
        return $this->connection->affectedRows() > 0;
    }

    /**
     * getUsers
     *
     * @return array
     */
    public function getUsers()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY level ASC, account ASC";
        return $this->connection->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
    }

    /**
     * getUser
     *
     * @param string $account account
     *
     * @return array
     */
    public function getUser($account)
    {
        $sql = "SELECT * FROM {$this->table} WHERE account=:account";
        return $this->connection->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, ['account' => $account]);
    }

    /**
     * removeSession
     *
     * @param string $account account
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function removeSession($account)
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();

        $cache = $di->get("cache");
        $cache->removeUserSession($account);
    }
}
