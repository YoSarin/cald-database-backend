<?php
namespace App\Exception;

abstract class Http extends \App\Exception
{
    protected static $httpCode = null;

    public function __construct($message = "", $code = 0, $previous = null)
    {
        if (empty($code)) {
            $code = static::$httpCode;
        }
        parent::__construct($message, $code, $previous);
    }
}
