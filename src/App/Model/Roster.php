<?php
namespace App\Model;

class Roster extends \App\Model
{
    protected static $fields = ["id", "team_id", "tournament_belongs_to_league_and_division_id", "seeding", "final_result", "finalized"];

    public static function create($teamId, $tournamentBelongsToLeagueAndDivisionId)
    {
        $i = new self();
        $i->setTeamId($teamId);
        $i->setTournamentBelongsToLeagueAndDivisionId($tournamentBelongsToLeagueAndDivisionId);

        return $i;
    }
    
    public function getTournament() {
        return Tournament::load(
            ["tournament_belongs_to_league_and_division.id" => $this->getTournamentBelongsToLEagueAndDivisionId()],
            null, 0,
            [
                "[><]tournament_belongs_to_league_and_division" => ["id" => "tournament_id"]
            ]
        );
    }
}
