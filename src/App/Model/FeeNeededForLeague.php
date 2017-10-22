<?php
namespace App\Model;

class FeeNeededForLeague extends \App\Model
{
    protected static $fields = ["id", "league_id", "fee_id"];

    protected static function getExplicitCondtions()
    {
        return [self::table() . ".active" => true];
    }
}
