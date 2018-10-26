<?php
namespace App\Model;
use App\Exception\WrongParam;

class Fee extends \App\Model
{
    protected static $fields = ["id", "name", "amount", "type"];

    protected static $types = ['player_per_season','player_per_tournament','team_per_season','team_per_tournament'];

    public static function create($name, $amount, $type) {
        if (!in_array($type, self::$types)) {
            throw new WrongParam("Type has to be one of [".(join(",", self::$types))."]");
        }

        $i = new self();
        $i->setAmount($amount);
        $i->setName($name);
        $i->setType($type);
        return $i;
    }
}
