<?php

$fp = fopen("DATA.txt", "r");

$i = 0;
while ($record = getRecord($fp)) {
    $i++;
    $arr = json_decode($record, 1);
    // echo $arr['START_TIME'], "\n";
    $arr['START_TIME'] = convDate($arr['START_TIME']);
    $arr['END_TIME'] = convDate($arr['END_TIME']);
    $arr['ONSALE_START_DATE'] = convDate($arr['ONSALE_START_DATE']);
    $arr['ONSALE_END_DATE'] = convDate($arr['ONSALE_END_DATE']);
    $arr['PUB_DATE'] = convDate($arr['PUB_DATE']);

    // gohappy 與 superstore 的字串不處理
    $arr['categories'] = convCategory($arr['CATEGORY_ID']);
    /*
    $x = explode('>',$arr['FULL_CATEGORY_PATH']);
    echo bin2hex($x[0]);
    echo "\n";
    echo bin2hex($x[1]);
    echo "\n";
    exit;
    */

    $arr['docid'] = $arr['PRODUCT_ID'];
    print_r($arr);
    sleep(1);
}

function convCategory($str)
{
    $ret = [];
    $catArry = explode(';', $str);
    foreach ($catArry as $cat) {
        $arr = explode('>', $cat);
        preg_match("/(\d+)/", $arr[0], $matches);
        $arr[0] = $matches[0];
        preg_match("/(\d+)/", $arr[1], $matches);
        $arr[1] = $matches[0];
        $ret[] = $arr;
    }
    return $ret;
}

function convDate($str)
{
    if (!$str) {
        return null;
    }

    $str = str_replace(["月"," "], ["",""], $str);
    list($day, $mon, $year) = sscanf($str, "%d-%d-%d");
    $year += 2000;

    return sprintf("%04d-%02d-%02d", $year, $mon, $day);

}

function getRecord($fp)
{
    $data = '';
    while($buf = fgets($fp)) {
        // echo $buf;
        $data .= $buf;
        if ($buf[0] == "}") {
            return $data;
        }
    }
}

?>
