<?php
namespace App\Controller;

use App\Model\Player;
use App\Model\Fee;
use App\Model\Season;
use App\Model\Tournament;
use App\Model\PlayerFeeChange;
use App\Model\FeeNeededForLeague;
use App\Model\TournamentBelongsToLeagueAndDivision;
use App\Exception\Http\Http404;

class Admin extends \App\Common
{
    public function createTournament($request, $response, $args)
    {
        list($name, $date, $location, $duration, $season_id, $league_ids, $division_ids) = $request->requireParams(
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
        $id = $request->requireParams(["id"]);

        $t = Tournament::load(["id" => $id])[0];
        $t->updateByRequest($request);
        $t->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $t->getExtendedData()],
            200
        );
    }

    public function deleteTournament($request, $response, $args)
    {
        $id = $request->requireParams(["id"]);

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

    public function pardonFee($request, $response, $args)
    {
        list($playerId, $seasonId) = $request->requireParams(["player_id", "season_id"]);
        if (!Player::exists(["id" => $playerId])) {
            throw new Http404("player does not exist");
        }
        if (!Season::exists(["id" => $seasonId])) {
            throw new Http404("season does not exist");
        }

        $feeChange = PlayerFeeChange::create($playerId, $seasonId, 0);
        $feeChange->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $feeChange->getData()],
            200
        );
    }

    public function cancelPardonFee($request, $response, $args)
    {
        list($id) = $request->requireParams(["pardon_id"]);

        $feeChange = PlayerFeeChange::loadById($id);
        if (empty($feeChange)) {
            throw new Http404("this fee pardon does not exist");
        }

        $feeChange->delete();

        return $this->container->view->render(
            $response,
            ['status' => 'OK'],
            200
        );
    }

    public function getFee($request, $response, $args)
    {
        list($id) = $request->requireParams(["season_id"]);

        if (!Season::exists(['id' => $id])) {
            throw new Http404("No such season");
        }

        $tournaments = Tournament::loadTournamentsInSeasonWithFees($id);

        return $this->container->view->render(
            $response,
            [
                'status' => 'OK',
                'data' => array_map(
                    function ($i) {
                        return $i->getData();
                    },
                    $tournaments
                )
            ],
            200
        );
    }
}
