<?php
namespace App\Controller;

use App\Model\User as UserModel;
use App\Model\UserHasPrivilege;

class Test extends \App\Common
{
    public function test($request, $response, $args)
    {
        $tournaments = \App\Model\Tournament::load(["season_id" => 16]);
        return $this->container->view->render($response, ["data" => $tournaments->getExtendedData()], 200);
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
