<?php

namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;

/**
 * Search base
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class SearchBase
{
    protected $client = null;
    protected $index;
    protected $type;

    /**
     * 設定 index type
     *
     * @param string $index index
     *
     * @return $this
     */
    public function setIndex($index)
    {
        if (!$index || !is_string($index)) {
            throw new \Exception("index 為空或非字串");
        }

        $this->index = $index;
        return $this;
    }

    /**
     * 設定 type
     *
     * @param mixed $type type
     *
     * @return $this
     */
    public function setType($type)
    {
        if (!$type || !is_string($type)) {
            throw new \Exception("type 為空或非字串");
        }
        $this->type = $type;
        return $this;
    }

    /**
     * 設定 Search Provider
     *
     * @param object $client Search client
     *
     * @return void
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function getClient()
    {
        if (null === $this->client) {
            // @codeCoverageIgnoreStart
            $this->client = new SearchClient();
        }

        // @codeCoverageIgnoreEnd
        return $this->client;
    }

    /**
     * 取得文件
     *
     * @param string $docId docId
     * @param array  $type  type
     * @param array  $index index
     *
     * @return array
     */
    public function getDoc($docId, $type = null, $index = null)
    {
        if (!$index) {
            $index = $this->index;
        }

        if (!$type) {
            $type = $this->type;
        }

        if (!$type || !$index) {
            throw new \Exception("no index or type");
        }

        $params = [
            'index'  => $index,
            'type'   => $type,
            'id'     => $docId,
            'client' => [ 'ignore' => [400, 404] ],
         ];

        $client = $this->getClient();
        $ret = $client->get($params);
        $response['result'] = $ret;
        if ($ret['found']) {
            $response['status'] = 200;
        } else {
            $response['status'] = 404;
        }
         return $response;
    }
}
