<?php
namespace App\Controller;

use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;

class Player extends \App\Common
{
    public function create(\Slim\Http\Request $request, $response, $args)
    {
        $request->requireParams(["first_name", "last_name", "birth_date", "sex"]);

        $firstName = trim($request->getParam("first_name"));
        $lastName = trim($request->getParam("last_name"));
        $birthDate = trim($request->getParam("birth_date"));
        $sex = trim($request->getParam("sex"));
        $email = trim($request->getParam("email"));
        $phone = trim($request->getParam("phone"));

        $user = UserModel::loggedUser($request->getToken());

        $p = \App\Model\Player::create($firstName, $lastName, $sex, $email, $birthDate);
        if (!empty($email)) {
            Validator::email()->assert($email);
            $p->setEmail($email);
        }
        if (!empty($phone)) {
            $p->setPhone($phone);
        }
        $p->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', "id" => $p->getId()],
            200
        );
    }

    public function update(\Slim\Http\Request $request, $response, $args)
    {
        $playerId = $request->requireParams(["player_id"]);

        $firstName = trim($request->getParam("first_name"));
        $lastName = trim($request->getParam("last_name"));
        $birthDate = trim($request->getParam("birth_date"));
        $sex = trim($request->getParam("sex"));
        $email = trim($request->getParam("email"));
        $phone = trim($request->getParam("phone"));

        $user = UserModel::loggedUser($request->getToken());

        $p = \App\Model\Player::loadById($playerId);
        if (!empty($email)) {
            Validator::email()->assert($email);
            $p->setEmail($email);
        }
        if (!empty($phone)) {
            $p->setPhone($phone);
        }
        if (!empty($sex)) {
            $p->setSex($sex);
        }
        if (!empty($birthDate)) {
            $p->setBirthDate($birthDate);
        }
        if (!empty($lastName)) {
            $p->setLastName($lastName);
        }
        if (!empty($firstName)) {
            $p->setFirstName($firstName);
        }

        $p->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player updated', "data" => $p->getData()],
            200
        );
    }

    public function listAll(\Slim\Http\Request $request, $response, $args)
    {
        $data = \App\Model\Player::load();
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => array_map( function ($item) {
                return $item->getData();
            }, $data)],
            200
        );
    }
}
