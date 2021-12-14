<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;
use \App\Exception\WrongParam;

class PlayerAtRoster extends \App\Model
{
    protected static $fields = ["id", "player_id", "roster_id", "role"];
    
    protected static $allowedRoles = ["player", "captain", "spirit_captain", "medical", "coach", "other_support"];
    
    const DEFAULT_ROLE = "player";

    public static function create($playerId, $rosterId, $role = PlayerAtRoster::DEFAULT_ROLE)
    {
        $i = new self();
        $i->setPlayerId($playerId);
        $i->setRosterId($rosterId);
        $i->setRole($role);
        $i->setSince(date("Y-m-d H:i:s", time()));

        return $i;
    }

    public static function allInSeason($seasonId, $playerId = null)
    {
        $condition = [
            "AND" => [
                "tournament.deleted" => false,
                "tournament.season_id" => $seasonId
            ]
        ];

        if ($playerId != null) {
            $condition["AND"]["player_id"] = $playerId;
        }

        return self::load(
            $condition,
            null,
            0,
            [
                "[><]roster(roster)" => ["roster.id" => "roster_id"],
                "[><]tournament_belongs_to_league_and_division(tournament_belongs_to_league_and_division_id)" => ["tournament_belongs_to_league_and_division.id" => "tournament_belongs_to_league_and_division_id"],
                "[><]tournament(tournament)" => ["tournament.id" => "tournament_belongs_to_league_and_division.tournament_id"],
            ]
        );
    }

    protected function onSaveValidation()
    {
        if (!in_array($this->getRole(), self::$allowedRoles)) {
            throw new WrongParam("Role {$this->getRole()} does not exist.");
        }
        if (self::exists(["player_id" => $this->getPlayerId(), "roster_id" => $this->getRosterId(), "role" => $this->getRole()])) {
            throw new Duplicate("Player is already part of this team with same role");
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
