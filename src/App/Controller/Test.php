<?php
namespace App\Controller;

use App\Model\User as UserModel;
use App\Model\UserHasPrivilege;

class Test extends \App\Common
{
    public function test($request, $response, $args)
    {
        $data = [];

        $seasonId = 16;
        $season = \App\Model\Season::loadById($seasonId);

        $tournaments = \App\Model\Tournament::load(["tournament.season_id" => $season->getId()]);
        $tournamentIds = array_map(function ($tournament) { return $tournament->getId(); }, $tournaments);

        $leagues = \App\Model\TournamentBelongsToLeagueAndDivision::load(["id" => $tournamentIds]);
        $leagueIds = array_map(function ($league) { return $league->getLeagueId(); }, $leagues);

        $feeForLeagues = \App\Model\FeeNeededForLeague::load(
            [
                "AND" => [
                    "league_id" => $leagueIds, "season.start[<=]" => $season->getStart()
                ], "ORDER" => ["season.start" => "DESC"]
            ],
            1,
            0,
            ["[><]season" => ["since_season" => "id"]]
        );

        $feeIds = array_map(function ($fee) { return $fee->getFeeId(); }, $feeForLeagues);
        $fees = \App\Model\Fee::load(["id" => $feeIds]);

        $players = \App\Model\Player::loadAllObjects(
            [
                "player_at_team.valid" => true,
                "tournament_belongs_to_league_and_division.tournament_id" => $tournamentIds,
                "player_at_team.team_id" => 10,
            ],
            null,
            0,
            [
                "[><]player_at_roster" => ["player.id" => "player_id"],
                "[><]roster" => ["player_at_roster.roster_id" => "id"],
                "[><]tournament_belongs_to_league_and_division" => ["roster.tournament_belongs_to_league_and_division_id" => "id"],
                "[><]player_at_team" => ["id" => "player_id"],
                "[><]tournament" => ["tournament_belongs_to_league_and_division.tournament_id" => "id"],
            ]
        );

        $teams = \App\Model\Team::load();

        $data = array_map(function ($fee) { return $fee->getData(); }, $fees);

        return $this->container->view->render($response, ["data" => [
            "leagueIds" => $leagueIds,
            "ffl" => array_map(function ($fee) { return $fee->getData(); }, $feeForLeagues),
            "fees" => $data,
            "teams" => array_map(function ($t) { return $t->getData(); }, $teams),
            "players" => array_map(function ($r) { return array_map(function ($p) { return $p->getData(); }, $r); }, $players),
        ]], 200);
    }

    public function team($request, $response, $args)
    {
        return $this->container->view->render($response, ["OK"], 200);
    }

    public function hs($request, $response, $args)
    {
        return $this->container->view->render($response, ["OK"], 200);
    }
}
