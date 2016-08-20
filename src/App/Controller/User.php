<?php
namespace App\Controller;

use App\Common;
use Slim\Http\Request;

class User extends \App\Common
{
    public function newUser(\Slim\Http\Request $request, $response, $args)
    {
        $this->requireParams($request, ["email", "password"]);
        $this->render($response, $request->getParams(), 200);
    }
}
