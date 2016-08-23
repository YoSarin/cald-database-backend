<?php
namespace App\Controller;

use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;

class Team extends \App\Common
{
    public function create(\Slim\Http\Request $request, $response, $args)
    {
        $this->requireParams($request, ["name"]);

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

        $user = UserModel::loggedUser($request->getParam('token'));

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
}
