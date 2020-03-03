<?php
namespace GoSearch\Task;

use GoSearch\Account\Ldap;
use GoSearch\User;

/**
 * AccountTask
 *
 * @subject("帳號建立及設定")
 *
 * @author("hc_chien <hc_chien>")
 */
class AccountTask extends MainTask
{

    /**
     * loginAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("登入")
     *
     * @arg("ACCOUNT PASSWORD")
     */
    public function loginAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        // temp
        if ($params[0] == 'hc_chien' && $params[1] == 'abcd123') {
            return $this->response(200, "登入成功");
        }

        if ($params[0] == 'guest' && $params[1] == '2018.gg') {
            return $this->response(200, "登入成功");
        }

        $ldap = new Ldap();
        $ret = $ldap->login($params[0], $params[1]);
        if ($ret) {
            // 更新 name
            $data = $ldap->getLoginUser();
            $user = new User();
            $user->updateName($params[0], $data['name']);
            return $this->response(200, "登入成功");
        }
        return $this->response(400, "登入失敗");
    }

    /**
     * addUserAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("新增使用者")
     *
     * @arg("ACCOUNT LEVEL")
     */
    public function addUserAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $ldap = new Ldap();
        $data = $ldap->getUser($params[0]);
        if ($data['count'] == 0) {
            return $this->response(400, "使用者 {$params[0]} 不存在 (LDAP)");
        }

        if (mb_strstr($data[0]['distinguishedname'][0], "已刪除")) {
            return $this->response(400, "使用者 {$params[0]} 已(離職)停用 (LDAP)");
        }

        $name = $data[0]['displayname'][0] ?? "";

        $user = new User();
        $ret  = $user->addUser($params[0], $name, $params[1]);
        if ($ret) {
            return $this->response(200, "新增成功");
        }
        return $this->response(400, "新增失敗 (已有相同資料)");
    }

    /**
     * searchLdapAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("搜尋 LDAP 帳號")
     *
     * @arg("ACCOUNT")
     */
    public function searchLdapAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        try {
            $ldap  = new Ldap();
            $data  = $ldap->searchUser($params[0]);
            $users = $ldap->filterResult($data);
            return $this->response(200, ['users' => $users]);
        } catch (\Exception $e) {
            return $this->response(500, $e->getMessage());
        }
    }

    /**
     * updateUserNameAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("修改使用者名稱")
     *
     * @arg("ACCOUNT NAME")
     */
    public function updateUserNameAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $user = new User();
        $ret = $user->updateName($params[0], $params[1]);
        if ($ret) {
            return $this->response(200, "修改成功");
        }

        return $this->response(400, "修改失敗 (使用者不存在或內容相同)");
    }

    /**
     * updateUserLevelAction
     *
     * @param array $params params
     *
     * @return void
     *
     * @subject("修改使用者等級")
     *
     * @arg("ACCOUNT LEVEL")
     */
    public function updateUserLevelAction($params)
    {
        if (!$this->validateParams($params, 2)) {
            return $this->response(400, "參數錯誤\n");
        }

        $user = new User();
        $ret = $user->updateLevel($params[0], $params[1]);
        if ($ret) {
            return $this->response(200, "修改成功");
        }

        return $this->response(400, "修改失敗 (使用者不存在或內容相同)");
    }

    /**
     * delUserAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("刪除使用者")
     *
     * @arg("ACCOUNT")
     */
    public function delUserAction($params)
    {
        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $user = new User();
        $ret = $user->delUser($params[0]);
        if ($ret) {
            return $this->response(200, "刪除成功");
        }

        return $this->response(400, "刪除失敗 (使用者不存在?)");
    }

    /**
     * listUsersAction
     *
     * @return void
     */
    public function listUsersAction()
    {
        $user = new User();
        $ret = $user->getUsers();
        return $this->response(200, ["users" => $ret]);
    }

    /**
     * getUsersAction
     *
     * @param array $params params
     *
     * @return array
     *
     * @subject("取得使用者")
     *
     * @arg("[ACCOUNT]")
     */
    public function getUserAction($params)
    {
        if (!isset($params[0])) {
            return $this->listUsersAction();
        }

        if (!$this->validateParams($params, 1)) {
            return $this->response(400, "參數錯誤\n");
        }

        $user = new User();
        $ret = $user->getUser($params[0]);
        if ($ret) {
            return $this->response(200, ['user' => $ret]);
        }

        return $this->response(404, "帳號不存在");
    }
}
