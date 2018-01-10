<?php
namespace App\Controller;

use \App\Model\PlayerAtTeam;
use \App\Model\UserHasPrivilege;
use \App\Model\User as UserModel;
use \App\Model\Player;
use \App\Exception\Http\Http400;
use \Respect\Validation\Exceptions\SizeException;
use \Respect\Validation\Validator;

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
            ['status' => 'OK', 'info' => 'Team created', "data" => $t->getData()],
            200
        );
    }

    public function update(\App\Request $request, $response, $args) {
        $teamId = $request->requireParams(["team_id"]);

        $name = trim($request->getParam("name"));
        $city = trim($request->getParam("city"));
        $www = trim($request->getParam("www"));
        $email = trim($request->getParam("email"));

        if (!empty($email)) {
            Validator::email()->assert($email);
        }

        if (!empty($www)) {
            Validator::url()->assert($www);
        }

        $user = UserModel::loggedUser($request->getToken());

        $t = \App\Model\Team::loadById($teamId);
        if (!empty($name)) {
            $t->setName($name);
        }
        if (!empty($city)) {
            $t->setCity($city);
        }
        if (!empty($www)) {
            $t->setWww($www);
        }
        if (!empty($email)) {
            $t->setEmail($email);
        }
        $t->save();

        $up = UserHasPrivilege::create($user->getId(), UserHasPrivilege::PRIVILEGE_EDIT, UserHasPrivilege::ENTITY_TEAM, $t->getId());
        $up->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Team updated', "data" => $t->getData()],
            200
        );
    }

    public function addPlayer(\App\Request $request, $response, $args)
    {
        list($teamId, $playerId, $seasonId) = $request->requireParams(["team_id", "player_id", "season_id"]);
        $player = \App\Model\Player::loadById($playerId);
        $team = \App\Model\Team::loadById($teamId);
        $season = \App\Model\Season::loadById($seasonId);

        if (\App\Model\PlayerAtTeam::exists(['player_id' => $player->getId(), 'valid' => true])) {
            throw new \App\Exception\Http\Http400($player->getFullName() . ' is still active in some other team.');
        }
        if (\App\Model\PlayerAtTeam::exists(['player_id' => $player->getId(), 'last_season[>=]' => $seasonId])) {
            throw new \App\Exception\Http\Http400($player->getFullName() . ' was in season ' . $season->getName() . ' active in another team.');
        }
        $playerAtTeam = \App\Model\PlayerAtTeam::create($player->getId(), $team->getId(), $season->getId());
        $playerAtTeam->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', "data" => $playerAtTeam->getData()],
            200
        );
    }

    public function removePlayer(\App\Request $request, $response, $args)
    {
        list($teamId, $playerId, $seasonId) = $request->requireParams(["team_id", "player_id", "season_id"]);
        $player = \App\Model\Player::loadById($playerId);
        $team = \App\Model\Team::loadById($teamId);
        $season = \App\Model\Season::loadById($seasonId);
        $contracts = \App\Model\PlayerAtTeam::load(['player_id' => $player->getId(), 'team_id' => $team->getId(), 'valid' => true]);
        if (count($contracts) <= 0) {
            throw new \App\Exception\Http\Http400($player->getFullName() . ' is not active in this team.');
        }

        foreach ($contracts as $playerAtTeam) {
            $playerAtTeam->setValid(false);
            $playerAtTeam->setLastSeason($season->getId());
            $playerAtTeam->save();
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player removed from team'],
            200
        );
    }

    public function addUserPriviledge(\App\Request $request, $response, $args)
    {
        list($teamId, $userId, $privilege) = $request->requireParams(['team_id', 'user_id', 'privilege']);

        if (!in_array($privilege, [\App\Model\UserHasPrivilege::PRIVILEGE_EDIT, \App\Model\UserHasPrivilege::PRIVILEGE_VIEW])) {
            throw new \App\Exception\Http\AppExceptionHttpHttp400("wrong privilege");
        }

        $alreadyCan = \App\Model\UserHasPrivilege::load([
            "AND" => [
                "user_id" => $userId,
                "OR" => [
                    "AND" => [
                        "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                        "entity_id"  => $teamId,
                        "privilege" => [
                            \App\Model\UserHasPrivilege::PRIVILEGE_EDIT,
                            $privilege,
                        ]
                    ],
                    "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_ADMIN,
                ]
            ]
        ]);

        if (empty($alreadyCan)) {
            $newPrivilege = \App\Model\UserHasPrivilege::create($userId, $privilege, \App\Model\UserHasPrivilege::ENTITY_TEAM, $teamId);
            $newPrivilege->save();
        }
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Privilege created (or already present)'],
            200
        );
    }

    public function removeUserPriviledge(\App\Request $request, $response, $args)
    {
        list($teamId, $userId, $privilege) = $request->requireParams(['team_id', 'user_id', 'privilege']);

        if (!in_array($privilege, [\App\Model\UserHasPrivilege::PRIVILEGE_EDIT, \App\Model\UserHasPrivilege::PRIVILEGE_VIEW])) {
            throw new \App\Exception\Http\AppExceptionHttpHttp400("wrong privilege");
        }

        $toRemove = \App\Model\UserHasPrivilege::load([
            "AND" => [
                "user_id" => $userId,
                "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                "entity_id"  => $teamId,
                "privilege" => $privilege,
            ]
        ]);

        if (!empty($toRemove)) {
            $toRemove[0]->delete();
            return $this->container->view->render(
                $response,
                ['status' => 'OK', 'info' => 'Privilege removed'],
                200
            );
        } else {
            return $this->container->view->render(
                $response,
                ['status' => 'Not found', 'info' => 'nothing to remove'],
                404
            );
        }
    }

    public function getFee(\App\Request $request, $response, $args) {
        list($teamId, $seasonId) = $request->requireParams(['team_id', 'season_id']);

        if (!\App\Model\Season::exists(['id' => $seasonId])) {
            throw new \App\Exception\Http\Http404("No such season");
        }
        if (!\App\Model\Team::exists(['id' => $teamId])) {
            throw new \App\Exception\Http\Http404("No such team");
        }

        $data = \App\Model\Team::getFee($seasonId, $teamId);
        return $this->container->view->render(
            $response,
            [
                'status' => 'OK',
                'data' => $data
            ],
            200
        );
    }
}
