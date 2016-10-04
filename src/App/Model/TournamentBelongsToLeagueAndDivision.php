<?php
namespace App\Model;

class TournamentBelongsToLeagueAndDivision extends \App\Model
{
    protected static $fields = ["id", "league_id", "division_id", "tournament_id"];

    public static function create($tournament_id, $league_id, $division_id)
    {
        $tld = new self();
        $tld->setTournamentId($tournament_id);
        $tld->setLeagueId($league_id);
        $tld->setDivisionId($division_id);
        return $tld;
    }
}
