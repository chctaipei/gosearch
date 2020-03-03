<?php
namespace GoSearch\Rule;

/**
 * ProductRule
 *
 * @author hc_chien <hc_chien>
 */
class NullRule implements RuleInterface
{

    /**
     * 產生準備排序用的 array
     *
     * @param array $order order array
     *
     * @return array
     */
    public function prepareSort($order = null)
    {
        return null;
    }

    /**
     * 產生準備過濾用的 array
     *
     * @param array $filter 過濾條件
     *
     * @return array
     */
    public function prepareFilter($filter = null)
    {
        return null;
    }
}
