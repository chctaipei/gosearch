<?php

namespace GoSearch\Rule;

/**
 * Product filter/sort rule for search
 *
 * @author hc_chien <hc_chien@hiiir.com>
 */
class ProductRule implements RuleInterface
{

    /**
     * order 增加 categoryId => $categoryId
     * ex: ['categoryId' => 123, 'watchTimes' => 'desc' ...];
     * 'top.rank' 指定區/館的 id, 照置頂的分數排序
     */
    private $allowSortColumn = [
                                '_score',
                                'sort',
                                'saleTimes',
                                'salePrice',
                                'updateTime',
                                'watchTimes',
                                'startTime',
                                'dailySaleTimes',
                                'dailyWatchTimes',
                                'categoryId'];
    private $allowOrderType = ['desc' => 'desc', 'des' => 'desc', 'asc' => 'asc'];

    /**
     * 取得可以排序的欄位名稱, 如不行則回傳 false
     *
     * @param array $sortColumn sort column
     *
     * @return string
     */
    private function getSortColumn($sortColumn)
    {
        if (in_array($sortColumn, $this->allowSortColumn)) {
            return $sortColumn;
        }

        return false;
    }

    /**
     * 取得排序升降名稱, 如不行則回傳 false
     *
     * @param array $orderType order type
     *
     * @return string
     */
    private function getOrderType($orderType)
    {
        $orderType = strtolower($orderType);
        if (isset($this->allowOrderType[$orderType])) {
            return $this->allowOrderType[$orderType];
        }

        return false;
    }

    /**
     * 產出過濾用的 array
     *
     * @param array $category 目錄
     *
     * @return array
     */
    private function getCategoryQueryTerm($category)
    {
        $categoryPath = explode('/', $category);
        array_shift($categoryPath);
        for ($i = 0; $i < 4; ++$i) {
            if (array_key_exists($i, $categoryPath)) {
                $query[]['term']['categories.lv'.$i] = trim(urldecode($categoryPath[$i]));
            }
        }

        return $query;
    }

    /**
     * 轉小寫過濾前後空白
     *
     * @param mixed $rule array or string
     *
     * @return mixed $rule
     */
    private function toLower($rule)
    {
        if (is_string($rule)) {
            $rule = trim(mb_strtolower($rule, 'utf-8'));
        } else {
            foreach ($rule as $key => $value) {
                $rule[$key] = trim(mb_strtolower($value, 'utf-8'));
            }
        }

        return $rule;
    }

    /**
     * 產出過濾條件 (品牌, 快搜)
     * 說明範例:
     *     品牌 A, B
     *     風格 C, D
     *     顏色 E, F
     * 邏輯 = (brand:A | brand:B) + (tag:C | tag:D) + (tag:E | tag:F)
     *
     * @param string $term 名稱 ('brandDetail', 'quick_xx')
     * @param array  $rule 條件
     *
     * @return query string
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function handleField($term, $rule)
    {
        $name = $term;

        // 品牌 - brandName
        if ('brandName' == $term) {
            $name = 'brandName';
            $rule = $this->toLower($rule);
        }

        // 品牌 - brandAlias
        if ('brandAlias' == $term) {
            $name = 'brandAlias.raw';
            $rule = $this->toLower($rule);
        }

        // 快搜
        if (strstr($term, 'quick_')) {
            $name = 'tags.raw';
            $rule = $this->toLower($rule);
        }

        if (is_array($rule)) {
            $query['terms'][$name]       = $rule;
            $query['terms']['execution'] = 'or';
        } else {
            $query['term'][$name] = $rule;
        }

        return $query;
    }

    /**
     * 置頂排序
     *
     * @param int $categoryId 館/區的分類 id
     *
     * @return array
     */
    private function setTopOrder($categoryId)
    {
        return [
                'nested_path'   => 'top',
                'nested_filter' => [
                    'term' => [
                        'top.categoryId' => $categoryId
                    ]
                ],
                'order'         => 'desc'
        ];
    }

    /**
     * 產生準備排序用的 array
     *
     * @param array $order order array
     *
     * @return array
     */
    public function prepareSort($order)
    {
        $sort = null;

        // handle order and sort
        if (is_array($order) && count($order) > 0) {
            foreach ($order as $key => $type) {
                $sortColumn = $this->getSortColumn($key);

                if (!$sortColumn) {
                    continue;
                }

                if ($sortColumn == 'categoryId') {
                    $sort['top.score'] = $this->setTopOrder($type);
                    continue;
                }

                $orderType = $this->getOrderType($type);
                if ($orderType) {
                    $sort[$sortColumn] = $orderType;
                }
            }
        }

        return $sort;
    }

    /**
     * 產生準備過濾用的 array
     *
     * @param array $filter 過濾條件
     *
     * @return array
     */
    public function prepareFilter($filter)
    {
        $esfilter = null;

        foreach ($filter as $key => $value) {
            if ($key == 'category' && !empty($value)) {
                $esfilter[]['nested'] = [
                    'path'   => 'categories',
                    'filter' => [
                        'bool' => [
                            'must' => $this->getCategoryQueryTerm($value),
                        ],
                    ],
                ];
                continue;
            }

            if ($this->getSortColumn($key)) {
                $esfilter[]['range'][$key] = $value;
                continue;
            }

            $esfilter[] = $this->handleField($key, $value);
        }//end foreach

        return $esfilter;
    }
}
