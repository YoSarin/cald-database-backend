<?php
namespace App\Controller;

use App\Model\User;

class Test extends \App\Common
{
    public function test($request, $response, $args)
    {
        $u = new \App\Model\User();
        $u->setEmail("test");
        $u->setPassword("asdasdads");
        $u->setState(User::STATE_CONFIRMED);
        $u->save();
        return $this->container->view->render($response, $u->getData(), 200);
    }
}
