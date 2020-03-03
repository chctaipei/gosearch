<?php

$db_username = "websearch";
$db_password = "F11cSearch";

$tns = <<<EOF
(DESCRIPTION =
    (ADDRESS = 
        (PROTOCOL = TCP)
        (HOST=10.97.11.163)
        (PORT=1521)
    )
    (CONNECT_DATA = 
        (SERVER=DEDICATED)
        (SERVICE_NAME=isorcl)
    )
)
EOF;

$db = "oci:dbname=$tns;charset=UTF8";

try {
    $dbh = new PDO($db,$db_username,$db_password);
}catch(PDOException $e){
    echo ($e->getMessage());
    exit;
}

/*
$sql = "SELECT * FROM nls_database_parameters WHERE parameter LIKE '%CHARACTERSET%'";

foreach ($dbh->query($sql) as $row) {
    print_r($row);
}
exit;
*/

// SELECT SMALL_IMAGE FROM SEARCH_PRODUCT_2017 WHERE SUBSTR(SMALL_IMAGE, 1, 4) = 'http';
// SELECT FLOOR(PRODUCT_ID/30000) FROM SEARCH_PRODUCT_2017;

$sql = <<<EOF
SELECT 
  PRODUCT_ID as "docid", 
  TO_CHAR(START_TIME, 'YYYY-MM-DD') as START_TIME, 
  TO_CHAR(END_TIME, 'YYYY-MM-DD') as END_TIME, 
  TO_CHAR(ONSALE_START_DATE, 'YYYY-MM-DD') as ONSALE_START_DATE, 
  TO_CHAR(ONSALE_END_DATE, 'YYYY-MM-DD') as ONSALE_END_DATE, 
  TO_CHAR(PUB_DATE, 'YYYY-MM-DD') as PUB_DATE,
  REPLACE(
   REPLACE('[' || REGEXP_REPLACE(CATEGORY_ID, 'M([[:digit:]]*)>S([[:digit:]]*):>([[:digit:]]*)>([[:digit:]]*)>?([[:digit:]]*)', '{"mid":\\1,"sid":\\2,"cid1":\\3,"cid2":\\4,"cid3":\\5}') || ']', ';', ','),
   '"cid3":}', '"cid3":null}') as "categories",
  MALL_ID,
  SID,
  STORE_ID,
  CATEGORY_ID,
  PRODUCT_ID,
  SHELF_ID,
  FULL_CATEGORY_PATH,
  NAME_LABEL,
  PRODUCT_NAME,
  DESC_BRIEF,
  BRAND,
  MARKET_PRICE,
  MEMBER_PRICE,
  ONSALE_PRICE,
  EXCHANGE_POINT,
  PARTIAL_PRICE,
  PARTIAL_POINT,
  SEARCH_KEYWORD,
  SMALL_IMAGE,
  IS_ONSALE,
  AUTHOR,
  PUBLISHER,
  ISBN,
  MAIN_CATEGORY_ID,
  DEVICE,
  ISPRD_CATEGORY_ONSALE_TYPE,
  CARD_PAYTIMES,
  SHIP_TYPE,
  PRODUCT_COMMENT,
  PROFIT,
  PROFIT_RATE,
  NOW_PRICE
FROM SEARCH_PRODUCT_2017
EOF;
$stmt = $dbh->prepare($sql);
$stmt->execute();

$i = 0;
$fp = fopen("DATA.txt", "w");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
//    print_r($row);
//    continue;
    // echo mb_detect_encoding($row['FULL_CATEGORY_PATH']);
    // echo $row['FULL_CATEGORY_PATH'];
    $str = json_encode($row, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";
    if (!$str) {
        echo json_last_error_msg();
        exit;
    }

    ++$i;
    fputs ($fp, $str);
    echo "$i\n";
}

fclose ($fp);
