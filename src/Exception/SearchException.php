<?php
namespace GoSearch\Exception;

use GoSearch\Exception\BaseException;

/**
 * Search Exception
 */
class SearchException extends BaseException
{

    /**
     * Construct function
     *
     * @param string $message  訊息
     * @param string $code     error Code 預設 null
     * @param object $previous previous Exception
     * @param int    $httpCode HTTP status code
     *
     * @return void
     * @throws Exception If $code not found
     */
    public function __construct(
        $message = "",
        $code = null,
        \Exception $previous = null,
        $httpCode = null
    ) {
        self::setConfigPath(realpath(__DIR__ . '/../../config/error'));
        parent::__construct($message, $code, $previous, $httpCode);
    }
}
?>
