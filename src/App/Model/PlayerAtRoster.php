<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;

class PlayerAtRoster extends \App\Model
{
    protected static $fields = ["id", "player_id", "roster_id"];

    public static function create($playerId, $rosterId)
    {
        $i = new self();
        $i->setPlayerId($playerId);
        $i->setRosterId($rosterId);
        $i->setSince(date("Y-m-d H:i:s", time()));

        return $i;
    }

    protected function onSaveValidation()
    {
        if (self::exists(["player_id" => $this->getPlayerId(), "roster_id" => $this->getRosterId()])) {
            throw new Duplicate("Player is already part of this team");
        }
        $roster = \App\Model\Roster::load(["id" => $this->getRosterId()])[0];
        $otherRosters = \App\Model\Roster::load(["id[!]" => $this->getRosterId(), "tournament_belongs_to_league_and_division_id" => $roster->getTournamentBelongsToLeagueAndDivision()]);
        if (self::exists([
            "player_id" => $this->getPlayerId(),
            "roster_id" => [array_map(function ($item) {
                return $item->getId();
            }, $otherRosters)]
        ])) {
            throw new Duplicate("Player is already participating event at another team in same division");
        }
    }
}
