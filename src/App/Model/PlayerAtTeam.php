<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;

class PlayerAtTeam extends \App\Model
{
    protected static $fields = ["id", "player_id", "team_id", "since", "until", "valid"];

    public static function create($playerId, $teamId)
    {
        $i = new self();
        $i->setPlayerId($playerId);
        $i->setTeamId($teamId);
        $i->setSince(date("Y-m-d H:i:s", time()));

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
