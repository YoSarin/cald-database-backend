<?php
namespace App\Controller;

use App\Model\User as UserModel;
use App\Model\UserHasPrivilege;

class Test extends \App\Common
{
    public function test($request, $response, $args)
    {
        $joins = [];
        \App\Model\PlayerAtRoster::extendedJoins($joins);
        $data = \App\Model\PlayerAtRoster::load(["player_at_roster.id" => 8966], null, 0, $joins);
        return $this->container->view->render($response, ["data" => $data[0]->getExtendedData()], 200);
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
