<?php
namespace GoSearch;

use Phalcon\DI\FactoryDefault;

/**
 * Redis Cache
 **/
class Redis extends \Phalcon\Cache\Backend\Redis
{
    protected $statsKey = '_PHCR';


    /**
     * __construct
     *
     * @param object $frontend frontend
     * @param array  $options  options
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function __construct($frontend = null, $options = null)
    {
        if ($frontend == null) {
            $di     = FactoryDefault::getDefault();
            $config = $di->get("config");
            $frontend = new \Phalcon\Cache\Frontend\Data(
                [ "lifetime" => $config->gosearch->redis->lifetime ?? $config->redis->lifetime ]
            );
        }

        if ($options == null) {
            $di     = FactoryDefault::getDefault();
            $config = $di->get("config");
            $options = [
                'host'       => $config->gosearch->redis->host ?? $config->redis->host,
                'port'       => $config->gosearch->redis->port ?? $config->redis->port,
                'persistent' => true,
                'index'      => $config->gosearch->redis->db ?? $config->redis->db,
                'auth'       => $config->gosearch->redis->pwd ?? $config->redis->pwd ?? ''
            ];
        }

        return parent::__construct($frontend, $options);
    }

    /**
     * redis client
     *
     * @return object $redis
     */
    public function getRedis()
    {
        if (!$this->_redis) {
            $this->_connect();
        }

        return $this->_redis;
    }

    /**
     * keys
     *
     * @param string $key key
     *
     * @return array
     */
    public function keys($key)
    {
        return $this->getRedis()->keys("{$this->statsKey}{$key}*");
    }

    /**
     * remove user session
     *
     * @param string $account account
     *
     * @return void
     */
    public function removeUserSession($account)
    {
        $arr = $this->keys('user');
        if (!$arr) {
            return;
        }

        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

        foreach ($arr as $key) {
            $session = $this->_redis->get($key);
            session_decode($session);
            if ($account == ($_SESSION['auth']['account'] ?? false)) {
                $session = $this->_redis->delete($key);
            }
        }
        session_reset();
    }
}
