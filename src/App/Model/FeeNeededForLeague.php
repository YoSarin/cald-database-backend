<?php
namespace App\Model;

class FeeNeededForLeague extends \App\Model
{
    protected static $fields = ["id", "league_id", "fee_id", "since_season"];

    public static function create($feeId, $seasonId, $leagueId)
    {
        $f = new self();
        $f->setLeagueId($leagueId);
        $f->setFeeId($feeId);
        $f->setSinceSeason($seasonId);

        return $f;
    }
}
