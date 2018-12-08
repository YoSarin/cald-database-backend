<?php
namespace App\Model;

class Tournament extends \App\Model
{
    protected static $fields = ["id", "name", "date", "location", "duration", "season_id", "deleted", "organizing_team_id"];


    protected static function getExplicitCondtions()
    {
        return [self::table() . ".deleted" => false];
    }

    public static function create($name, $date, $location, $duration, $season_id, $organizing_team_id)
    {
        $t = new self();
        $t->setName($name);
        $t->setDate($date);
        $t->setLocation($location);
        $t->setDuration($duration);
        $t->setSeasonId($season_id);
        $t->setOrganizingTeamId($organizing_team_id);
        return $t;
    }
}
