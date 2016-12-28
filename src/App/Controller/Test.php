<?php
namespace App\Controller;

use App\Model\User as UserModel;
use App\Model\UserHasPrivilege;

class Test extends \App\Common
{
    public function test($request, $response, $args)
    {
        $user = UserModel::loggedUser($request->getToken());
        $p = UserHasPrivilege::create($user->getId(), UserHasPrivilege::PRIVILEGE_EDIT, UserHasPrivilege::ENTITY_TEAM, 1);
        $p->save();
        return $this->container->view->render($response, $p->getData(), 200);
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
