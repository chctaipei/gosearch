<?php
namespace Api;

use Phalcon\Mvc\Controller;

/**
 * UserController
 *
 * @property \Phalcon\Tag $tag
 *
 * @RoutePrefix("/api/user")
 */
class UserController extends \Base
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
     * [GET] 取得 user
     *
     * @param string $user user
     *
     * @return void
     *
     * @Get("/{user}", name="get-user")
     */
    public function getAction($user)
    {
        return $this->jsonOutput($this->callTask("Account", "getUser", [$user]));
    }

    /**
     * [POST] 新增 user 或修改 user 的等級
     *
     * @param string $user user
     *
     * @return void
     *
     * @Route("/{user}", methods={"POST"}, name="edit-user")
     */
    public function editAction($user)
    {
        $rawBody = $this->request->getJsonRawBody(true);
        $level  = $rawBody['level'];

        if ($level != 0 && $level != 1) {
            return $this->jsonOutput(["status" => 400, "message" => "權限錯誤"]);
        }

        $action = $rawBody['action'];
        if ($action == 'create') {
            return $this->jsonOutput($this->callTask("Account", "addUser", [$user, $level]));
        }

        if ($this->auth['account'] == $user) {
            return $this->jsonOutput(["status" => 400, "message" => "無法修改自己的等級"]);
        }

        if ($action == 'update') {
            return $this->jsonOutput($this->callTask("Account", "updateUserLevel", [$user, $level]));
        }

        return $this->jsonOutput(["status" => 400, "message" => "錯誤的指令"]);
    }

    /**
     * [DELETE] 刪除 user
     *
     * @param string $user user
     *
     * @return void
     *
     * @Route("/{user}", methods={"DELETE"}, name="del-user")
     */
    public function delAction($user)
    {
        if ($this->auth['account'] == $user) {
            return $this->jsonOutput(["status" => 400, "message" => "無法刪除自己的帳號"]);
        }

        return $this->jsonOutput($this->callTask("Account", "delUser", [$user]));
    }
}
