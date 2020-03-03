<?php

/**
 * 列出AD所有的帳戶
 *
 * @return void
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
function listECAccount()
{
    $host = "172.26.2.3";
    $username = "cn=ldapreader,cn=users,dc=hq,dc=hiiir";
    $password = "&ujm8ik,";
    $basedn = "ou=here,dc=hq,dc=hiiir";

    $ds = ldap_connect($host);
    if (!$ds) {
        return;
    }

    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

    @ldap_bind($ds, $username, $password);
    if ($ds) {
        $result = ldap_search($ds, $basedn, "(CN=*)");
        $info   = ldap_get_entries($ds, $result);

        // 帳戶
        $count = count($info);
        for ($i = 0; $i < $count; $i++) {
            if (isset($info[$i]["givenname"][0])) {
                echo $info[$i]["cn"][0] . ", ";
                echo $info[$i]["givenname"][0] . ", ";
                echo $info[$i]["displayname"][0] . ", ";
                echo $info[$i]["mail"][0] . ", ";
                echo $info[$i]["name"][0] . "<BR>" . PHP_EOL;
            }
        }
    }//end if
}

/**
 * ldapSpecialChars
 *
 * @param mixed $string string
 *
 * @return string
 */
function ldapSpecialChars($string)
{
    $sanitized = ['\\' => '\5c', '*' => '\2a', '(' => '\28', ')' => '\29', "\x00" => '\00' ];

    return strtr($string, $sanitized);
}

/**
 * LDAP 操作方法
 *
 * @param string $name 名稱
 *
 * @return array 名稱
 *
 * @throws SSOException If ERROR
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
function listAdAccountByName($name)
{
    $host = "172.26.2.3";
    $username = "cn=ldapreader,cn=users,dc=hq,dc=hiiir";
    $password = "&ujm8ik,";
    $basedn = "ou=here,dc=hq,dc=hiiir";

    $ladpConnection = ldap_connect($host);

    ldap_set_option($ladpConnection, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($ladpConnection, LDAP_OPT_PROTOCOL_VERSION, 3);

    @ldap_bind($ladpConnection, $username, $password);
    if ($ladpConnection) {
        $filters = "(CN=" . ldapSpecialChars($name) . "*)";
        $result  = ldap_search($ladpConnection, $basedn, $filters);
        $info    = ldap_get_entries($ladpConnection, $result);
        // print_r($info);
        // exit;

        $result = array();
        $limit  = 0;

        for ($i = 0; $i < $info && $limit < 10; $i++, $limit++) {
            if (isset($info[$i]["givenname"][0])) {
                $item = array();

                $item["cn"]          = $info[$i]["cn"][0];
                $item["mail"]        = $info[$i]["mail"][0];
                $item["givenname"]   = $info[$i]["givenname"][0];
                $item["name"]        = $info[$i]["name"][0];
                $item["displayname"] = $info[$i]["displayname"][0];
                $result[]            = $item;
            }
        }

        return $result;
    } else {
        throw new SSOException("301");
    }//end if
}

$param = ($argv[1]) ?? "";
print_r(listAdAccountByName($param));
?>
