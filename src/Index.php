<?php
namespace GoSearch;

use GoSearch\SearchBase;
use GoSearch\SearchClient;
use \Exception;

/**
 * Index Service
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Index extends SearchBase
{

    /**
     * init Index Type Mapping
     *
     * @return array
     */
    public function createIndexMapping()
    {
        return [
            'index'   => $this->client->createIndex($this->index),
            'mapping' => $this->client->addTypeMapping($this->type),
        ];
    }

    /**
     * delete Index
     *
     * @return array
     */
    public function deleteIndex()
    {
        return $this->client->deleteIndex($this->index);
    }

    /**
     * 大量更新(新增/刪除)文件 (搜尋)
     *
     * @param array  $feed    array of documents
     * @param string $param   ['index' => $index, 'type' => $type']
     * @param string $action  'create','index','delete','update'
     * @param string $docName mapping 到 docid, feed 如果已有設定 docid, 則不需要傳遞這個欄位
     *
     * @return array
     */
    public function bulk($feed, $param, $action = SearchClient::BULK_INDEX, $docName = null)
    {
        if ($docName) {
            $productArr = [];
            foreach ($feed as $productDoc) {
                $productDoc['docid'] = $productDoc[$docName];
                $productArr[]        = $productDoc;
            }
            $feed = $productArr;
        }

        $client = $this->getClient();
        $ret    = $client->bulk($feed, $param, $action);

        // 錯誤會記錄在 $ret['errors']
        return $ret;
    }

    /**
     * 更新(新增)文件
     *
     * @param string $docId docId
     * @param array  $body  document
     *
     * @return array
     */
    public function indexDoc($docId, $body)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => $body,
        ];

        if (null !== $docId) {
            $params['id'] = $docId;
        }

        $client = $this->getClient();
        return $client->index($params);
    }

    /**
     * 更新部分資料
     *
     * @param string $docId docId
     * @param array  $body  部分資料
     *
     * @return void
     */
    public function updateDoc($docId, $body)
    {
        $client = $this->getClient();
        $params = [
            'id'    => $docId,
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => ['doc' => $body]
        ];

        \GoSearch\Helper\Message::debugLog("[updateDoc] 更新文件 $docId\n");
        $client = $this->getClient();
        return $client->update($params);
    }

    /**
     * 隔離文件
     * future 模式參考:
     * https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_future_mode.html
     *
     * @param string  $docId  document_id
     * @param boolean $future 使用 future, 做非同步更新
     *
     * @return result
     */
    public function deleteDoc($docId, $future = false)
    {
        $client = $this->getClient();
        $params = [
            'id'    => $docId,
            'index' => $this->index,
            'type'  => $this->type,
        ];

        if ($future) {
            $params['client']['future'] = 'lazy';
        }

        \GoSearch\Helper\Message::debugLog("[deleteDoc] 刪除文件 $docId\n");
        return $client->remove($params);
    }
}
