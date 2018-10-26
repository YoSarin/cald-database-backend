<?php
namespace App\Controller;
use App\Model;
use App\Exception\Http\Http400;
use App\Model\Fee;

class Season extends \App\Common
{
    public function createSeason($request, $response, $args)
    {
        list($name, $start) = $request->requireParams(["name", "start"]);
        if(\App\Model\Season::Exists(["name" => $name])) {
            throw new Http400("Season with same name already exists");
        }

        if(\App\Model\Season::Exists(["start[=<]" => $start])) {
            throw new Http400("There already is newer season");
        }

        $s = \App\Model\Season::Create($name, $start);
        $s->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Season created', 'data' => $s->getData()],
            200
        );
    }

    public function updateSeason($request, $response, $args)
    {
        list($id) = $request->requireParams(["season_id"]);
        $season = \App\Model\Season::loadById($id);
        $name = trim($request->getParam("name"));
        $start = trim($request->getParam("start"));
        if (!empty($name)) {
            $season->setName($name);
        }

        if(!empty($start)) {
            $season->setStart($start);
        }
        $season->save();
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Season updated', 'data' => $season->getData()],
            200
        );
    }

    public function createFee($request, $response, $args)
    {
        list($amount, $name, $type) = $request->requireParams(["amount", "name", "type"]);
        $fee = \App\Model\Fee::create($name, $amount, $type);
        $fee->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'fee created', 'data' => $fee->getData()],
            200
        );
    }

    public function deleteFee($request, $response, $args)
    {
        list($id) = $request->requireParams(["id"]);
        $fee = \App\Model\Fee::loadByid($id);
        $fee->delete();
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Fee has been deleted'],
            200
        );
    }

    public function updateFee($request, $response, $args)
    {
        list($id) = $request->requireParams(["id"]);
        $fee = \App\Model\Fee::loadByid($id);

        $name = trim($request->getParam("name"));
        $amount = trim($request->getParam("amount"));
        $type = trim($request->getParam("type"));

        if (!empty($name)) {
            $fee->setName($name);
        }
        if(!empty($amount)) {
            $fee->setAmount($amount);
        }
        if(!empty($type)) {
            $fee->setType($type);
        }

        $fee->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'fee created', 'data' => $fee->getData()],
            200
        );
    }

    public function activateFee($request, $response, $args)
    {
        list($feeId, $seasonId, $leagueId) = $request->requireParams(["fee_id", "first_season_id", "league_id"]);

        if (\App\Model\FeeNeededForLeague::exists(["fee_id" => $feeId, "since_season[<=]" => $seasonId, "league_id" => $leagueId])) {
            throw new Http400("Given combination already exists");
        }

        $feeStart = \App\Model\FeeNeededForLeague::create($feeId, $seasonId, $leagueId);
        $feeStart->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'fee/season/league link created', 'data' => $feeStart->getData()],
            200
        );
    }
}
