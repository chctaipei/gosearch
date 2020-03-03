<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * ProjectController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/api/document")
 */
class DocumentController extends \Base
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
    }

    /**
     * [GET] 取得文件
     *
     * @param string $project project
     * @param string $type    type
     * @param string $docId   docId
     *
     * @return void
     *
     * @Get("/{project}/{type}/{docId}")
     */
    public function getDocAction($project, $type, $docId)
    {
        return $this->jsonOutput($this->callTask("Search", "getDoc", [$project, $type, $docId]));
    }

    /**
     * [PUT] 修改文件
     *
     * @param string $project project
     * @param string $type    type
     * @param string $docId   docId
     *
     * @return void
     *
     * @Route("/{project}/{type}/{docId}", methods={"PUT"})
     */
    public function putDocAction($project, $type, $docId)
    {
        if ($this->checkAuth() == false) {
            return;
        }
        $doc = $this->request->getJsonRawBody(true);
        return $this->jsonOutput($this->callTask("Index", "updateDoc", [$project, $type, $docId, $doc]));
    }

    /**
     * [POST] 增加或替換掉文件
     *
     * @param string $project project
     * @param string $type    type
     * @param string $docId   docId
     *
     * @return void
     *
     * @Route("/{project}/{type}/{docId}", methods={"POST"})
     */
    public function updateDocAction($project, $type, $docId)
    {
        if ($this->checkAuth() == false) {
            return;
        }
        $doc = $this->request->getJsonRawBody(true);
        return $this->jsonOutput($this->callTask("Index", "indexDoc", [$project, $type, $docId, $doc]));
    }
}
