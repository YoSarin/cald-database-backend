<?php
namespace App\Controller;

use App\Model\User as UserModel;
use App\Model\UserHasPrivilege;

class Test extends \App\Common
{
    public function test($request, $response, $args)
    {
        $data = [];

        $tournaments = \App\Model\Tournament::load(["tournament.season_id" => 16]);
        $tournamentIds = array_map(function ($tournament) { return $tournament->getId(); }, $tournaments);

        $leagues = \App\Model\TournamentBelongsToLeagueAndDivision::load(["id" => $tournamentIds]);
        $leagueIds = array_map(function ($league) { return $league->getLeagueId(); }, $leagues);

        $feeForLeagues = \App\Model\FeeNeededForLeague::load(["league_id" => $leagueIds]);
        $feeIds = array_map(function ($fee) { return $fee->getFeeId(); }, $feeForLeagues);

        $fees = \App\Model\Fee::load(["id" => $feeIds]);

        $data = array_map(function ($fee) { return $fee->getData(); }, $fees);

        return $this->container->view->render($response, ["data" => $data], 200);
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
