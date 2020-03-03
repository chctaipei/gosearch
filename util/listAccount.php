<?php

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
?>
