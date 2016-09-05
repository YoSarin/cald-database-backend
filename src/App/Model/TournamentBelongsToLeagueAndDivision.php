<?php
namespace App\Model;

class TournamentBelongsToLeagueAndDivision extends \App\Model
{
    protected static $fields = ["id", "league_id", "division_id", "tournament_id"];
}
