<?php
namespace App\Exception\Http;

class Http403 extends \App\Exception\Http
{
    protected static $httpCode = 403;
    protected static $defaultMessage = "Not Authorized";
}
