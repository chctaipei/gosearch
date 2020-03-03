<?php

namespace GoSearch\Helper;

use GoSearch\SearchClient;

/**
 * Import Helper
 *
 * 目前只有用在 import file
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Import
{
    const CLEAN_UNUSED_TAG = true;

    /**
     * fetchFile
     *
     * @param string $file file
     *
     * @return array
     */
    public static function fetchFile($file)
    {
        $fp    = fopen($file, "r");
        $arr   = [];
        $count = 0;
        $buf   = self::getRecord($fp);
        foreach ($buf as $record) {
            $data = json_decode($record, 1);
            if (!$data) {
                echo "bad record\n";
                echo $record, "\n";
                continue;
            }

            $arr[] = $data;
            if (++$count >= 1000) {
                yield($arr);
                $count = 0;
                $arr   = [];
            }
        }

        if ($count) {
            yield($arr);
        }

        fclose($fp);
    }

    /**
     * import 文件 by 檔案
     * gohappy 與 superstore 的字串不處理:
     * explode('>',$data['FULL_CATEGORY_PATH']);
     *
     * @param string $file filename
     *
     * @return array
     */
    public static function fetchGoHappyProduct($file)
    {
        $fp    = fopen($file, "r");
        $arr   = [];
        $count = 0;
        $buf   = self::getRecord($fp);
        foreach ($buf as $record) {
            $data = json_decode($record, 1);
            if (!$data) {
                echo "bad record\n";
                echo $record, "\n";
                continue;
            }
            $data['START_TIME'] = self::convDate($data['START_TIME']);
            $data['END_TIME'] = self::convDate($data['END_TIME']);
            $data['ONSALE_START_DATE'] = self::convDate($data['ONSALE_START_DATE']);
            $data['ONSALE_END_DATE'] = self::convDate($data['ONSALE_END_DATE']);
            $data['PUB_DATE'] = self::convDate($data['PUB_DATE']);

            // clean unused tag
            if (self::CLEAN_UNUSED_TAG) {
                unset($data['CATEGORY_ID_NAME']);
                unset($data['CATEGORY_NAME']);
                unset($data['STORE_NAME']);
                unset($data['SUPPLIER_NAME']);
                unset($data['DESC_DESCRIPTION']);
                unset($data['DESC_SPECIFICATION']);
                unset($data['SHIP_TYPE_ID']);
            }

            // docId
            $data['docid'] = $data['PRODUCT_ID'];
            // bulk action
            // $data['bulk_action'] = SearchClient::BULK_INDEX;
            if (!$data['CATEGORY_ID']) {
                echo "資料有誤，分類不存在\n";
                print_r($record);
            } else {
                $data['categories'] = self::convCategory($data['CATEGORY_ID']);
            }

            $arr[] = $data;
            if (++$count >= 1000) {
                yield($arr);
                $count = 0;
                $arr   = [];
            }
        }//end foreach

        if ($count) {
            yield($arr);
        }

        fclose($fp);
    }

    /**
     * getRecord
     *
     * @param mixed $fp file pointer
     *
     * @return string
     */
    private static function getRecord($fp)
    {
        $data = '';
        while ($buf = fgets($fp, 10240)) {
            $data .= $buf;
            if ($buf[0] == "}") {
                yield $data;
                $data = '';
            }
        }
    }

    /**
     * 轉換分類文字為 array
     *
     * @param string $str 分類
     *
     * ex: "M1>S1:>1041>24708>1572;M1>S1:>1041>24708>341860
     * => [[1,1,1041,24708], [1,1,1041,24708,341860]]
     *
     * @return array
     */
    private static function convCategory($str)
    {
        $ret = [];
        $catArry = explode(';', $str);
        foreach ($catArry as $cat) {
            $obj = (object) [];
            $arr = explode('>', $cat);
            preg_match("/(\d+)/", $arr[0], $matches);
            $obj->mid = $matches[0];
            preg_match("/(\d+)/", $arr[1], $matches);
            $obj->sid = $matches[0];
            $obj->cid1 = isset($arr[2]) ? $arr[2] : null;
            $obj->cid2 = isset($arr[3]) ? $arr[3] : null;
            $obj->cid3 = isset($arr[4]) ? $arr[4] : null;
            // 去除重複的分類
            $ret[$cat] = $obj;
        }
        return array_values($ret);
    }

    /**
     * 轉換日期格式
     *
     * @param string $str 日期
     *
     * ex: "11-5月 -16"
     * => "2016-05-11"
     *
     * @return string
     */
    private static function convDate($str)
    {
        if (!$str) {
            return null;
        }

        $str = str_replace(["月", " "], ["", ""], $str);
        list($day, $mon, $year) = sscanf($str, "%d-%d-%d");
        $year += 2000;

        return sprintf("%04d-%02d-%02d", $year, $mon, $day);
    }
}
