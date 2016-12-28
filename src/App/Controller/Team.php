<?php
namespace App\Controller;

use App\Model\PlayerAtTeam;
use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;
use App\Model\Player;
use App\Exception\Http\Http400;

class Team extends \App\Common
{
    public function create(\App\Request $request, $response, $args)
    {
        $request->requireParams(["name"]);

        $name = trim($request->getParam("name"));
        $city = trim($request->getParam("city"));
        $www = trim($request->getParam("www"));
        $email = trim($request->getParam("email"));
        $foundedAt = trim($request->getParam("founded_at"));

        if (!empty($email)) {
            Validator::email()->assert($email);
        }

        if (!empty($www)) {
            Validator::url()->assert($www);
        }

        $user = UserModel::loggedUser($request->getToken());

        $t = \App\Model\Team::create($name, $city, $www, $email, $foundedAt);
        $t->save();

        $up = UserHasPrivilege::create($user->getId(), UserHasPrivilege::PRIVILEGE_EDIT, UserHasPrivilege::ENTITY_TEAM, $t->getId());
        $up->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Team created', "id" => $t->getId()],
            200
        );
    }

    public function addPlayer(\App\Request $request, $response, $args)
    {
        $params = $request->requireParams(["team_id", "player_id"]);
        $player = \App\Model\Player::loadById($params['player_id']);
        $team = \App\Model\Team::loadById($params['team_id']);

        if (\App\Model\PlayerAtTeam::exists(['player_id' => $player->getId(), 'valid' => true])) {
            throw new \App\Exception\Http\Http400($player->getFullName() . ' is already active in some other team.');
        }
        $playerAtTeam = \App\Model\PlayerAtTeam::create($player->getId(), $team->getId());
        $playerAtTeam->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', "data" => $playerAtTeam->getData()],
            200
        );
    }

    public function removePlayer(\App\Request $request, $response, $args)
    {
        $params = $request->requireParams(["team_id", "player_id"]);
        $player = \App\Model\Player::loadById($params['player_id']);
        $team = \App\Model\Team::loadById($params['team_id']);
        $contracts = \App\Model\PlayerAtTeam::load(['player_id' => $player->getId(), 'team_id' => $team->getId(), 'valid' => true]);
        if (count($contracts) <= 0) {
            throw new \App\Exception\Http\Http400($player->getFullName() . ' is not active in this team.');
        }

        foreach ($contracts as $playerAtTeam) {
            $playerAtTeam->setValid(false);
            $playerAtTeam->save();
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player removed from team'],
            200
        );
    }
}
