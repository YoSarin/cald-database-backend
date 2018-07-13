<?php
namespace App\Model;

class Address extends \App\Model
{
    protected static $fields = ["id", "type", "player", "city", "street", "zip_code", "country"];
}
