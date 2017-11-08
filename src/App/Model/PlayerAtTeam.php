<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;

class PlayerAtTeam extends \App\Model
{
    protected static $fields = ["id", "player_id", "team_id", "first_season", "last_season", "valid"];

    public static function create($playerId, $teamId, $seasonId)
    {
        $i = new self();
        $i->setPlayerId($playerId);
        $i->setTeamId($teamId);
        $i->setFirstSeason($seasonId);

        return $i;
    }

    protected function onSaveValidation()
    {
        if ($this->getIsValid() && self::exists(["player_id" => $this->getPlayerId(), "valid" => true])) {
            throw new Duplicate("Player is already active in another team");
        }
    }

    protected static function getExplicitCondtions()
    {
        return [self::table() . ".valid" => true];
    }
}
