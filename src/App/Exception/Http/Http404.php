<?php
namespace App\Exception\Http;

class Http404 extends \App\Exception\Http
{
    protected static $httpCode = 404;
    protected static $defaultMessage = "Not Found";
}
