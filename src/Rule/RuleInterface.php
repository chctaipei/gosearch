<?php
namespace GoSearch\Rule;

/**
 * ProductRule
 *
 * @author hc_chien <hc_chien@hiiir.com>
 */
interface RuleInterface
{

    /**
     * 產生準備排序用的 array
     *
     * @param array $order order array
     *
     * @return array
     */
    public function prepareSort($order);

    /**
     * 產生準備過濾用的 array
     *
     * @param array $filter 過濾條件
     *
     * @return array
     */
    public function prepareFilter($filter);
}
