<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * SearchController
 *
 * @RoutePrefix("/api/search")
 **/
class SearchController extends \Base
{

   /**
    * Initialize controller
    *
    * @return void
    */
    public function initialize()
    {
    }

    /**
     * [POST] 搜尋文件 BY scriptId
     *
     * @param string $project  project
     * @param string $type     type
     * @param string $scriptId scriptId
     *
     * @return void
     *
     * @Route("/{project}/{type}/_search/{scriptId}", methods={"POST","GET"}, name="api-search-template")
     */
    public function searchTemplateAction($project, $type, $scriptId)
    {
        $json = $this->request->getJsonRawBody(true);
        return $this->jsonOutput($this->callTask("Search", "searchTemplate", [$project, $type, $scriptId, $json]));
    }

    /**
     * [POST|GET] 搜尋文件
     *
     * @param string $project project
     * @param string $type    index type
     *
     * @return void
     *
     * @Route("/{project}/{type}/_search", methods={"POST","GET"}, name="api-search-query")
     */
    public function searchAction($project, $type)
    {
        $json = $this->request->getJsonRawBody(true);
        return $this->jsonOutput($this->callTask("Search", "search", [$project, $type, $json]));
    }

    /**
     * [POST] 紀錄 Keyword, matches 到熱門關鍵字
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}/_log", methods={"POST"}, name="api-search-log")
     */
    public function logAction($project)
    {
        $json = $this->request->getJsonRawBody(true);
        $keyword = $json['keyword'] ?? "";
        $matches = $json['matches'] ?? 0;
        return $this->jsonOutput($this->callTask("Search", "log", [$project, $keyword, $matches]));
    }

    /**
     * [POST] 紀錄 docid, keyword 點擊紀錄
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}/_click", methods={"POST"}, name="api-search-click")
     */
    public function clickAction($project)
    {
        $json    = $this->request->getJsonRawBody(true);
        $docid   = $json['docid'] ?? 0;
        $keyword = $json['keyword'] ?? "";
        return $this->jsonOutput($this->callTask("Search", "click", [$project, $docid, $keyword]));
    }

    /**
     * [POST|GET] 熱門關鍵字
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}/_hotwords", methods={"POST","GET"}, name="api-search-hotwords")
     */
    public function hotwordsAction($project)
    {
        $json = $this->request->getJsonRawBody(true);
        $size = $json['size'] ?? 10;
        return $this->jsonOutput($this->callTask("Search", "listHot", [$project, $size]));
    }

    /**
     * [POST] 模糊搜尋相關關鍵字
     * 範例:
     * {
     * "keyword": "便當"
     * }
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}/_fuzzy", methods={"POST"}, name="api-search-fuzzy")
     */
    public function fuzzywordsAction($project)
    {
        $json = $this->request->getJsonRawBody(true);
        $keyword = $json['keyword'] ?? '';
        return $this->jsonOutput($this->callTask("Search", "fuzzy", [$project, $keyword]));
    }

    /**
     * [POST] 建議關鍵字 (autocomplete)
     * 範例:
     * {
     * "keyword": "a"
     * }
     *
     * @param string $project project
     *
     * @return void
     *
     * @Route("/{project}/_suggest", methods={"POST"}, name="api-search-suggest")
     */
    public function suggestAction($project)
    {
        $json = $this->request->getJsonRawBody(true);
        $keyword = $json['keyword'] ?? '';
        return $this->jsonOutput($this->callTask("Search", "suggest", [$project, $keyword]));
    }
}
