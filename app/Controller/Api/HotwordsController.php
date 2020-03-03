<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * SearchController
 *
 * @RoutePrefix("/api/hotwords")
 **/
class HotwordsController extends \Base
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
     * beforeExecuteRoute
     *
     * @return boolean
     */
    public function beforeExecuteRoute()
    {
        return $this->checkAuth();
    }

    /**
     * [PUT] 修改分數
     *
     * @param mixed $project project
     *
     * @return void
     *
     * @Route("/{project}", methods={"PUT"}, name="api-hotwords-update")
     */
    public function updateAction($project)
    {
        $value = $this->request->getJsonRawBody(true);
        $query = $value['query'];
        $count = $value['count'];
        return $this->jsonOutput($this->callTask("Search", "updateHot", [$project, $query, $count]));
    }

    /**
     * [POST] 新增過濾
     *
     * @param mixed $project project
     *
     * @return void
     *
     * @Route("/{project}/filter", methods={"POST"}, name="api-hotwords-filter")
     */
    public function filterAction($project)
    {
        $value = $this->request->getJsonRawBody(true);
        $query = $value['query'];
        $this->callTask("Tool", "insertBadword", [$project, $query]);
        return $this->jsonOutput($this->callTask("Search", "updateHot", [$project, $query, 0]));
    }
}
