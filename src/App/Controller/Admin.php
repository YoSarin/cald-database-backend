<?php
namespace App\Controller;

use App\Model\Player;
use App\Model\Fee;
use App\Model\Season;
use App\Model\Tournament;
use App\Model\Nationality;
use App\Model\PlayerFeeChange;
use App\Model\TournamentBelongsToLeagueAndDivision;
use App\Exception\Http\Http404;
use App\Exception\Http\Http400;
use App\Exception\WrongParam;
use App\Context;

class Admin extends \App\Common
{
    public function createTournament($request, $response, $args)
    {
        list($name, $date, $location, $duration, $season_id, $league_ids, $division_ids, $organizing_team_id) = $request->requireParams(
            ["name", "date", "location", "duration", "season_id", "league_ids", "division_ids", "organizing_team_id"]
        );

        $t = Tournament::create($name, $date, $location, $duration, $season_id, $organizing_team_id);
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
        list($id) = $request->requireParams(["id"]);

        $t = Tournament::loadById($id);
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
        list($id) = $request->requireParams(["id"]);

        $t = Tournament::loadById($id);
        $t->setDeleted(true);
        $t->save();

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

        if (!\App\Model\Season::exists(['id' => $id])) {
            throw new \App\Exception\Http\Http404("No such season");
        }

        $data = \App\Model\Team::getFee($id);

        return $this->container->view->render(
            $response,
            [
                'status' => 'OK',
                'data' => $data
            ],
            200
        );
    }

    public function updateUser(\App\Request $request, $response, $args)
    {
        list($id) = $request->requireParams(["user_id"]);
        $login    = trim($request->getParam("login"));
        $password = trim($request->getParam("password"));
        $email    = trim($request->getParam("email"));
        $state    = trim($request->getParam("state"));

        $u = \App\Model\User::load(["id" => (int)$id])[0];

        if ($login) {
            $u->setLogin($login);
        }
        if ($email) {
            $u->setEmail($email);
        }
        if ($password) {
            $u->setPassword($password);
        }
        if ($state && in_array($state, \App\Model\User::allowedStates())) {
            $u->setState($state);
        } else if ($state) {
            throw new WrongParam("State must be one of '" . implode("', '", \App\Model\User::allowedStates()) . "'");
        }

        $u->save();

        return $this->container->view->render(
            $response,
            ["status" => "OK", "data" => $u->getData()],
            200
        );
    }


    public function addNationality($request, $response, $args)
    {
        list($name, $countryName, $isoCode) = $request->requireParams(["name", "country_name", "iso_code"]);

        if (\App\Model\Nationality::exists(["name" => $name])) {
            throw new Http400("Nationality '$name' already exists");
        }

        $n = \App\Model\Nationality::create($name, $countryName, $isoCode);
        $n->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $n->getData()],
            200
        );
    }


    public function updateNationality($request, $response, $args)
    {
        list($id) = $request->requireParams(["nationality_id"]);
        $name = trim($request->getParam("name"));
        $countryName = trim($request->getParam("country_name"));
        $isoCode = trim($request->getParam("iso_code"));

        $n = \App\Model\Nationality::loadById($id);
        if ($name) {
            $n->setName($name);
        }
        if ($countryName) {
            $n->setCountryName($countryName);
        }
        if ($isoCode) {
            $n->setIsoCode($isoCode);
        }
        $n->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK'],
            200
        );
    }

    public function deleteNationality($request, $response, $args)
    {
        list($id) = $request->requireParams(["nationality_id"]);
        $n = \App\Model\Nationality::loadById($id);
        $n->delete();

        return $this->container->view->render(
            $response,
            ['status' => 'OK'],
            200
        );
    }
}
