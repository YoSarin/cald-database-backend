<?php
namespace App\Model;

class Nationality extends \App\Model
{
    protected static $fields = ["id", "name", "country_name", "iso_code"];

    public static function create($name, $countryName, $isoCode) {
        $n = new self();
        $n->setName($name);
        $n->setCountryName($countryName);
        $n->setIsoCode($isoCode);
        return $n;
    }
}
