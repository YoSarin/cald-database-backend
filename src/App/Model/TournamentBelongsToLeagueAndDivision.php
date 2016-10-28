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

    public static function loadTournamentsInSeasonWithFees($seasonId)
    {
        return static::load(["AND" => ["season_id" => $seasonId, "fee_needed_for_league.valid" => true]], null, 0, [
            "[><]fee_needed_for_league" => ["tournament_belongs_to_league_and_division.league_id" => "league_id"],
            "[><]tournament" => ["tournament_belongs_to_league_and_division.tournament_id" => "id"]
        ]);
    }
}
