<?php
namespace App\Controller;

use App\Model\Tournament;
use App\Model\TournamentBelongsToLeagueAndDivision;

class Admin extends \App\Common
{
    public function createTournament($request, $response, $args)
    {
        list($name, $date, $location, $duration, $season_id, $league_ids, $division_ids) = $this->requireParams(
            $request,
            ["name", "date", "location", "duration", "season_id", "league_ids", "division_ids"]
        );

        $t = Tournament::create($name, $date, $location, $duration, $season_id);
        $t->save();
        $tldList = [];
        foreach ($league_ids as $league_id) {
            foreach ($division_ids as $division_id) {
                $tld = TournamentBelongsToLeagueAndDivision::create($t->getId(), $league_id, $division_id);
                $tld->save();
                $tldList[] = $tld->getExtendedData();
            }
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $tldList],
            200
        );
    }

    public function updateTournament($request, $response, $args)
    {
        $id = $this->requireParams($request, ["id"]);
    }

    public function deleteTournament($request, $response, $args)
    {
        $id = $this->requireParams($request, ["id"]);

        $tournaments = Tournament::load(["id" => $id]);
        foreach ($tournaments as $t) {
            $t->setDeleted(true);
            $t->save();
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK'],
            200
        );
    }
}
