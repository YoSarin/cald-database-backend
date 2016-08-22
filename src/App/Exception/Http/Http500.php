<?php
namespace App\Exception\Http;

class Http500 extends \App\Exception\Http
{
    protected static $httpCode = 500;
}
