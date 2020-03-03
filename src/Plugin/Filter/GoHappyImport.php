<?php

namespace GoSearch\Plugin\Filter;

/**
 * GoHappyImportFilter
 */
class GoHappyImport implements ImportFilter
{

    /**
     * filter
     *
     * @param array $data data
     *
     * @return array
     */
    public function filter($data)
    {
        // 將 categories 字串轉為 array
        if ($data['categories'] ?? false) {
            $data['categories'] = json_decode($data['categories'], 1);
        }

        return $data;
    }
}
