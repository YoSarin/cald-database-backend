<?php
namespace App\Model;

class Nationality extends \App\Model
{
    protected static $fields = ["id", "name", "country_name"];

    public static function create($name, $countryName) {
        $n = new self();
        $n->setName($name);
        $n->setCountryName($countryName);
        return $n;
    }
}
