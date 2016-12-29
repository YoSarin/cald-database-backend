<?php
namespace App\Model;

class Roster extends \App\Model
{
    protected static $fields = ["id", "team_id", "tournament_belongs_to_league_and_division_id", "seeding", "final_result"];

    public static function create($teamId, $tournamentBelongsToLeagueAndDivisionId)
    {
        $i = new self();
        $i->setTeamId($teamId);
        $i->setTournamentBelongsToLeagueAndDivisionId($tournamentBelongsToLeagueAndDivisionId);

        return $i;
    }
}
