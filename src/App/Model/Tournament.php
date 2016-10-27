<?php
namespace App\Model;

class Tournament extends \App\Model
{
    protected static $fields = ["id", "name", "date", "location", "duration", "season_id", "deleted"];


    protected static function getExplicitCondtions()
    {
        return ["deleted" => false];
    }

    public static function create($name, $date, $location, $duration, $season_id)
    {
        $t = new self();
        $t->setName($name);
        $t->setDate($date);
        $t->setLocation($location);
        $t->setDuration($duration);
        $t->setSeasonId($season_id);
        return $t;
    }

    public static function loadTournamentsInSeasonWithFees($seasonId)
    {
        return static::load(["season_id" => $seasonId], null, 0, [
            "[><]tournament_belongs_to_league_and_division (tld)" => ["id" => "tournament_id"],
            "[><]fee_needed_for_league (ffl)" => ["tld.league_id" => "league_id"]
        ]);
    }
}
