<?php
namespace App;

class Exception extends \Exception
{
    protected static $defaultMessage = "";

    public function __construct($message = "", $code = 0, $previous = null)
    {
        if (empty($message)) {
            $message = static::$defaultMessage;
        }
        parent::__construct($message, $code, $previous);
    }

    public static function getType(\Exception $exception)
    {
        return strtolower(str_replace('\\', '_', get_class($exception)));
    }
}
