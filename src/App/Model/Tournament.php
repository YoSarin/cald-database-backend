<?php
namespace App\Model;

class Tournament extends \App\Model
{
    protected static $fields = ["id", "name", "date", "location", "duration", "season_id", "deleted"];


    protected static function getExplicitConditions()
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
}
