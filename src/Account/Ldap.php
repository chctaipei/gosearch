<?php
namespace GoSearch\Account;

/**
 * LDAP
 *
 * @author hc_chien <hc_chien@hiiir.com>
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Ldap
{
    private $host;
    private $username;
    private $password;
    private $basedn;
    public $user;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $di = \Phalcon\DI\FactoryDefault::getDefault();

        $config = $di->get("config");
        $this->host     = $config->gosearch->ldap->host ?? $config->ldap->host ?? null;
        $this->username = $config->gosearch->ldap->username ?? $config->ldap->username ?? null;
        $this->password = $config->gosearch->ldap->password ?? $config->ldap->password ?? null;
        $this->basedn   = $config->gosearch->ldap->basedn ?? $config->ldap->basedn ?? null;
        if (null == $this->host) {
            throw new \Exception("無法連線, 請確認 ldap 設定");
        }
    }

    /**
     * connectLdap
     *
     * @return object
     */
    private function connectLdap()
    {
        $ldapConnection = ldap_connect("ldap://" . $this->host, 389);
        if (!$ldapConnection) {
            throw new \Exception("無法連線, 請確認 ldap 設定");
        }

        ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
        // LDAPv3 supports UTF-8
        ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);

        $bind = @ldap_bind($ldapConnection, $this->username, $this->password);
        if (!$bind) {
            throw new \Exception("無法連線, 請確認 ldap 設定");
        }

        return $ldapConnection;
    }

    /**
     * login
     *
     * @param string $account  account
     * @param string $password password
     *
     * @return boolean
     */
    public function login($account, $password)
    {
        $ldapConnection = $this->connectLdap();

        $filter        = "(sAMAccountName=$account)";
        $searchResults = @ldap_search(
            $ldapConnection,
            $this->basedn,
            $filter
        );

        if (!is_resource($searchResults)) {
            throw new \Exception("Error in search results.");
        }

        $entry = @ldap_first_entry($ldapConnection, $searchResults);
        if (!$entry) {
            return false;
        }
        $attr = ldap_get_attributes($ldapConnection, $entry);

        // 取出完整的帳戶定義
        $dn = @ldap_get_dn($ldapConnection, $entry);
        $bind = @ldap_bind($ldapConnection, $dn, $password);
        ldap_close($ldapConnection);

        if (!$bind || !isset($dn) || empty($password)) {
            return false;
            // throw new Exception("帳號或密碼錯誤");
        }

        $this->user['account'] = $account;
        $this->user['name']    = $attr['displayName'][0];
        return true;
    }

    /**
     * ldapSpecialChars
     *
     * @param string $string string
     *
     * @return string
     */
    private function ldapSpecialChars($string)
    {
        $sanitized = ['\\' => '\5c', '*' => '\2a', '(' => '\28', ')' => '\29', "\x00" => '\00' ];

        return strtr($string, $sanitized);
    }

    /**
     * getUser
     *
     * @param string $account 帳號
     *
     * @return array
     *
     * @throws Exception If ERROR
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function getUser($account)
    {
        return $this->searchUser($account, 1);
    }

    /**
     * 列出所有的帳戶
     *
     * ["givenname", "cn", "givenname", "displayname", "mail", "name"]
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function listAllAccount()
    {
        return $this->searchUser("", 0);
    }

    /**
     * searchUser
     *
     * @param string $prefix prefix
     * @param int    $exact  exactly
     *
     * @return array
     */
    public function searchUser($prefix, $exact = 0)
    {
        $ldapConnection = $this->connectLdap();
        $all = "";
        if (!$exact) {
            $all = "*";
        }

        $filters = "(CN=" . $this->ldapSpecialChars($prefix) . "$all)";
        $result  = ldap_search($ldapConnection, $this->basedn, $filters);
        $entries = ldap_get_entries($ldapConnection, $result);
        ldap_close($ldapConnection);

        return $entries;
    }

    /**
     * filter Result from searchUser
     *
     * @param array $data data
     *
     * @return array
     */
    public function filterResult($data)
    {
        $result = [];

        foreach ($data as $user) {
            if (!is_array($user)) {
                continue;
            }

            $tmp = [];
            foreach ($user as $key => $value) {
                if (isset($value['count'])) {
                    $tmp[$key] = $value[0];
                }
                $result[$user['cn'][0]] = $tmp;
            }
        }
        
        return $result;
    }

    /**
     * getLoginUser
     *
     * @return array
     */
    public function getLoginUser()
    {
        return $this->user;
    }
}
