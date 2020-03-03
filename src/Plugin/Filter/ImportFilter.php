<?php

namespace GoSearch\Plugin\Filter;

/**
 * import interface
 */
interface ImportFilter
{

    /**
     * filter
     *
     * @param array $data data
     *
     * @return array
     */
    public function filter($data);
}
