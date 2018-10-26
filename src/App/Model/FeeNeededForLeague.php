<?php
namespace App\Model;

class FeeNeededForLeague extends \App\Model
{
    protected static $fields = ["id", "league_id", "fee_id", "since_season", "valid"];

    protected static function getExplicitCondtions()
    {
        return [self::table() . ".valid" => true];
    }

    public static function create($feeId, $seasonId, $leagueId)
    {
        $f = new self();
        $f->setLeagueId($leagueId);
        $f->setFeeId($feeId);
        $f->setSinceSeason($seasonId);

        return $f;
    }
}
