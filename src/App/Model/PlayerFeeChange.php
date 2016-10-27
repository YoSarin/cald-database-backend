<?php
namespace App\Model;

class PlayerFeeChange extends \App\Model
{
    protected static $fields = ["id", "player_id", "season_id", "amount"];

    public static function create($playerId, $seasonId, $amount)
    {
        $a = new self();
        $a->setPlayerId($playerId);
        $a->setSeasonId($seasonId);
        $a->setAmount($amount);
        return $a;
    }
}
