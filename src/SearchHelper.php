<?php
namespace GoSearch;

use Elasticsearch\ClientBuilder;
use GoSearch\Rule\NullRule;

/**
 * Search Helper
 *
 * 1. 從 redis 撈取
 * 2. 從 db 撈取
 * 3. 載入 local file
 *
 * @author hc_chien <hc_chien@hiiir.com>
 */
class SearchHelper
{
    private static $arrBody = null;

    /**
     * 讀取 json config 檔案內容
     *
     * @param string $name 檔名
     * @param string $type index type
     *
     * @return array
     */
    public static function loadSchema($name, $type = '')
    {
        if (isset(self::$arrBody[$name])) {
            return self::$arrBody[$name];
        }

        $fname = __DIR__ . "/../config/$type/$name.json";
        self::$arrBody[$name] = json_decode(file_get_contents($fname), 1);
        return self::$arrBody[$name];
    }

    /**
    /**
     * 取得同音文字
     *
     * @param string $text 文字
     *
     * @return string
     */
    public static function getPhonetic($text)
    {
        $phoneticTable = self::loadSchema('phoneticData');
        return strtr($text, $phoneticTable);
    }

    /**
     * 取得停用字表
     *
     * @param string $char 停用字轉換為 $char 字元
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public static function getStopword($char = ' ')
    {
        $stopwordTable = self::loadSchema('stopwordData');
        if ($char != ' ') {
            foreach ($stopwordTable as $key => $value) {
                $stopwordTable[$key] = $char;
            }
        }

        return $stopwordTable;
    }

    /**
     * 過濾停用字詞 (stopword)
     *
     * @param string $text 本文
     * @param string $char 替換字元, 預設 ' '
     *
     * @return string 過濾後的文字
     */
    public static function replaceStopword($text, $char = ' ')
    {
        $stopwordTable = self::getStopword($char);
        return strtr($text, $stopwordTable);
    }

    /**
     * 是否包含布林邏輯符號 (+,-,*,",),~,\
     *
     * @param string $query 搜尋字串
     *
     * @return void
     */
    public static function hasLogical($query)
    {
        return (preg_match('/[\\\(\*\"\)~\|]+/', $query) ||
            preg_match('/[\+\-][^\s]/', $query));
    }

    /**
     * 過濾 query
     *
     * @param string  $query      搜尋字串
     * @param boolean $checkLogic 是否要判斷邏輯字元, 若有則回空字串
     *
     * @return string 過濾後的文字
     */
    public static function filterQuery($query, $checkLogic = false)
    {
        if ($checkLogic && self::hasLogical($query)) {
            return '';
        }

        mb_regex_encoding("UTF-8");
        $str = mb_strtolower($query);
        $str = strip_tags($str);

        // ex: %E9%9B%84%E8%9C%82%E7%B2%BE%E8%8F%AF%E6%B6%B2
        $pattern = "%[a-f0-9]{2}";
        $str     = mb_eregi_replace($pattern, '', $str);

        // ex: &#26377;&#32218;&#28961;&#32218;&#20841;&#29992;
        $pattern = "&#\d+;";
        $str     = mb_eregi_replace($pattern, '', $str);

        if ($checkLogic) {
            // 去掉非文字的字元(符號)
            $pattern = "([^\w^.^+^-^/]+)";
            $str     = mb_eregi_replace($pattern, ' ', $str);
        }

        // 合併空白
        $pattern = "([\s]+)";
        $str     = mb_eregi_replace($pattern, ' ', $str);

        return trim($str);
    }

    /**
     * 比對相似文字
     *
     * @param array   $text  array of text
     * @param string  $term  term
     * @param integer $limit 百分比 (預設 50)
     *
     * @return boolean
     */
    public static function similar($text, $term, $limit = 50)
    {
        foreach ($text as $txt) {
            similar_text($txt, $term, $percent);
            if ($percent >= $limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * 設定 filter/sort Rule
     *
     * @param string $name rule name
     *
     * @return param GoSearch\Service\Rule $ rule
     */
    public static function getRule($name)
    {
        $name     = ucfirst($name);
        $ruleName = "GoSearch\\Rule\\{$name}Rule";
        if (class_exists($ruleName)) {
             return new $ruleName();
        }

        return new NullRule();
    }

    /**
     * 標籤分類
     *
     * @param array $tagArr array of tags
     *
     * @return array
     */
    public static function classify($tagArr)
    {
        $color = "/(卡其|裸膚|水藍)$|" .
                 // 不要"潤色"
                 "[^潤](色)$|" .
                 // 顏色組合
                 "^([(桃|墨|粉|土|玫瑰|太空|麻|白|灰|綠|藍|灰|紅|黃|紫|黑|白|金|銀|橘|螢光|咖|米|\/|)]*)$|" .
                 // 咖啡, 不要"白咖啡","黑咖啡"
                 "^咖啡$/u";
        $age = "/(歲|月)(以上|以下)*$|" .
               // 生理階段
               "(幼兒|嬰兒|小童|中童|大童|全齡)/u";
        $size = "/(以下|以上|吋|公斤|公升|尺|號|坪|大尺碼|cm|kg|ml|單人|雙人|加大|特大|free)$|" .
                // 1000-2000
                "^(\d*-\d*)$|" .
                // 10-20g
                "^(\d*~\d*)g$|" .
                // 大小
                "^(\d*)(l|m|s|xl|xs|xxl|ml|w)$|" .
                // us 尺寸
                "^(us \d+.*)$/u";
        $price = "/(\d+元)$|" .
                 // 5000以上
                 "^(\d{4,}以[上|下])$|" .
                 // 5000-10000
                 "^(\d{1,}\s*-\d{4,})$/u";
        $size2 = "/^(\d*)(l|m|s|xl|xs|xxl|ml)$/";

        $ret['agg_price'] = [];
        $ret['agg_color'] = [];
        $ret['agg_age']   = [];
        $ret['agg_size']  = [];
        $ret['agg_model'] = [];
        foreach ($tagArr as $tag) {
            // 以下順序對結果有影響, size 安排在最後做
            if (preg_match($price, $tag)) {
                $ret['agg_price'][] = $tag;
                continue;
            }

            if (preg_match($color, $tag)) {
                $ret['agg_color'][] = $tag;
                continue;
            }

            if (preg_match($age, $tag)) {
                $ret['agg_age'][] = $tag;
                continue;
            }

            if (preg_match($size, $tag)) {
                if (preg_match($size2, $tag)) {
                    $tag = mb_strtoupper($tag, 'utf-8');
                }

                $ret['agg_size'][] = $tag;
                continue;
            }

            $ret['agg_model'][] = $tag;
        }//end foreach

        return $ret;
    }
}
