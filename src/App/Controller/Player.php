<?php
namespace App\Controller;

use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;

class Player extends \App\Common
{
    public function create(\Slim\Http\Request $request, $response, $args)
    {
        $this->requireParams($request, ["first_name", "last_name", "birth_date", "sex", "team_id"]);

        $firstName = trim($request->getParam("first_name"));
        $lastName = trim($request->getParam("last_name"));
        $birthDate = trim($request->getParam("birth_date"));
        $sex = trim($request->getParam("sex"));
        $email = trim($request->getParam("email"));
        $teamId = trim($request->getParam("team_id"));

        if (!empty($email)) {
            Validator::email()->assert($email);
        }

        $user = UserModel::loggedUser($request->getParam('token'));

        $p = \App\Model\Player::create($firstName, $lastName, $sex, $email, $birthDate);
        $p->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', "id" => $p->getId()],
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
