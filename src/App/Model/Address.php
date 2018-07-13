<?php
namespace App\Model;

class Address extends \App\Model
{
    protected static $fields = ["id", "type", "player_id", "city", "street", "zip_code", "country"];

    public static function create($type, $playerID, $city, $country) {
        $a = new self();
        $a->setType($type);
        $a->setPlayerId($playerID);
        $a->setCity($city);
        $a->setCountry($country);
        return $a;
    }
}
