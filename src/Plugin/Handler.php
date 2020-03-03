<?php
namespace GoSearch\Plugin;

/**
 * Class: Handler
 */
class Handler
{

    /**
     * get Filter Plugins
     *
     * @param string $type type
     *
     * @return array
     */
    public function getFilterPlugins($type)
    {
        $ret = [];
        if ($handle = opendir(__DIR__ . "/Filter")) {
            while (false !== ($entry = readdir($handle))) {
                if (preg_match("/(?P<name>^\w+$type)\.php$/", $entry, $matches)) {
                    $ret[] = $matches['name'];
                }
            }
            closedir($handle);
        }

        return $ret;
    }

    /**
     * get Import Filters
     *
     * @return array
     */
    public function getImportFilters()
    {
        return $this->getFilterPlugins("Import");
    }
}
