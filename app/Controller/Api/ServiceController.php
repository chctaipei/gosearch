<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * UserController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/api/service")
 */
class ServiceController extends \Base
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
     * [PUT] 啟用或停止 cron server
     *
     * @return void
     *
     * @Route("/cron", methods={"PUT"}, name="service-cron")
     */
    public function putCronAction()
    {
        $rawBody = $this->request->getJsonRawBody(true);
        $active  = $rawBody['active'] ?? 1;

        if ($active == 1) {
            return $this->jsonOutput($this->callTask("Cron", "start"));
        } else {
            return $this->jsonOutput($this->callTask("Cron", "stop"));
        }
    }
}
