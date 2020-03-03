<?php
namespace GoSearch;

use GoSearch\SearchClient;
use GoSearch\SearchHelper;

/**
 * 同義詞庫維護及處理
 *
 * @author hc_chien <hc_chien>
 */
class Phrase
{
    private $provider = null;

    /**
     * Initialize
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    private function getProvider()
    {
        if (null === $this->provider) {
            $this->provider = new SearchClient();
        }

        return $this->provider;
    }

    /**
     * 設定 Search Provider
     *
     * @param object $searchProvider Search provider
     *
     * @return void
     */
    public function setSearchProvider($searchProvider)
    {
        $this->provider = $searchProvider;
    }

    /**
     * 必須用","分隔關鍵字, 否則將產生後續處理問題
     * 同義詞至少要兩個以上才能做關聯, 也不可包含特殊字元
     *
     * @param string $words 同義詞, 格式: "AAA,BBB,CCC..."
     *
     * @return string $words 或 false
     */
    private function processWords($words)
    {
        $list = explode(',', mb_strtolower(trim($words), 'utf-8'));
        $list = array_unique($list);
        $arr  = [];
        foreach ($list as $value) {
            $value = trim($value);
            if ('' == $value) {
                continue;
            }

            $arr[] = $value;
        }

        if (count($arr) <= 1) {
            // 同義詞至少要兩個以上才能做關聯
            return false;
        }

        natsort($arr);
        // array_unique 有跳號的問題 ex: 0,1,3,4  ElasticSearch 無法處理
        // 因此需透過 array_values 修正
        return array_values($arr);
    }

    /**
     * 建立同義詞
     *
     * @param string $words 同義詞, 格式: "AAA,BBB,CCC..."
     * @param string $id    docId, 如果為空則新建
     *
     * @return string 'created' 或  'updated'
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function index($words, $id = null)
    {
        $words = $this->processWords($words);
        if (!$words) {
            return false;
        }

        $searchProvider = $this->getProvider();

        $body     = ['words' => $words];
        $response = $searchProvider->index($id, $body, 'phrase');
        return $response['created'] ? 'created' : 'updated';
    }

    /**
     * 搜尋同義詞
     *
     * @param array $param parameter
     *
     * @return array
     */
    private function doSearch($param)
    {
        $searchProvider = $this->getProvider();

        $responseArray     = $searchProvider->doSearch($param, ProductSearch::BY_PHRASE);
        $response['total'] = $responseArray['hits']['total'];
        foreach ($responseArray['hits']['hits'] as $arr) {
            $response['hits'][] = ['id' => $arr['_id'], 'words' => $arr['_source']['words']];
        }

        return $response;
    }

    /**
     * 搜尋同義詞
     *
     * @param string $keyword 同義詞, 格式: "AAA,BBB,CCC..."
     *
     * @return array
     */
    public function search($keyword)
    {
        $param = [
            'keyword' => SearchHelper::filterQuery($keyword),
        ];

        return $this->doSearch($param);
    }

    /**
     * 列出資料庫內的同義詞
     *
     * @param integer $page  頁數
     * @param integer $limit 每頁數量
     *
     * @return array
     */
    public function listing($page = 1, $limit = 10)
    {
        $param = [
            'page'  => $page,
            'limit' => $limit,
            'list'  => 1,
        ];

        return $this->doSearch($param);
    }

    /**
     * 刪除資料庫內的同義詞
     *
     * @param string $id docId
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function delete($id)
    {
        $searchProvider = $this->getProvider();

        // 刪除本商品
        return $searchProvider->remove($id, 'phrase');
    }
}
