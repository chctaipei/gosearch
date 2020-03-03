<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * SearchController
 *
 * @RoutePrefix("/api/badword")
 **/
class BadwordController extends \Base
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
     * [POST] 新增 (與 HotwordsController::filterAction() 差別在輸出)
     *
     * @param mixed $project project
     *
     * @return void
     *
     * @Route("/{project}", methods={"POST"}, name="api-badword-insert")
     */
    public function addAction($project)
    {
        $value = $this->request->getJsonRawBody(true);
        $query = $value['query'];

        // 將熱門詞的數字歸零
        $this->callTask("Search", "updateHot", [$project, $query, 0]);
        return $this->jsonOutput($this->callTask("Tool", "insertBadword", [$project, $query]));
    }

    /**
     * [DELETE] 刪除
     *
     * @param mixed $project project
     *
     * @return void
     *
     * @Route("/{project}", methods={"DELETE"}, name="api-badword-delete")
     */
    public function deleteAction($project)
    {
        $value = $this->request->getJsonRawBody(true);
        $query = $value['query'];
        return $this->jsonOutput($this->callTask("Tool", "deleteBadword", [$project, $query]));
    }
}
