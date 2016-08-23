<?php
namespace App\Controller;

use App\Common;
use App\Exception\Database\Duplicate;
use App\Exception\Database;
use App\Exception\User\Unconfirmed;
use App\Exception\Http\Http403;
use App\Model\Token;
use Slim\Http\Request;
use Respect\Validation\Validator;

class User extends \App\Common
{
    public function create(Request $request, $response, $args)
    {
        $this->requireParams($request, ["email", "password"]);

        $email = trim($request->getParam("email"));
        $password = trim($request->getParam("password"));

        Validator::email()->assert($email);
        Validator::stringType()->length(6, null)->assert($password);

        $user = \App\Model\User::create($email, $password);
        $user->save();

        $token = Token::create(Token::TYPE_EMAIL_VERIFICATION, $user->getId());
        $token->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'User created', "id" => $user->getId()],
            200
        );
    }

    public function login(Request $request, $response, $args)
    {
        $this->requireParams($request, ["email", "password"]);
        $email = trim($request->getParam("email"));
        $password = trim($request->getParam("password"));

        if (!\App\Model\User::exists(["email" => $email])) {
            throw new Http403();
        }

        $user = \App\Model\User::load(["email" => $email])[0];
        /** @var $user \App\Model\User */
        if ($user->verifyPassword($password) && $user->canLogin()) {
            $token = Token::create(Token::TYPE_LOGIN, $user->getId());
            $token->save();
            return $this->container->view->render(
                $response,
                ["token" => $token->getData()],
                200
            );
        } elseif ($user->verifyPassword($password)) {
            throw new Unconfirmed("User has not verified email yet");
        }

        return $this->container->view->render($response, ["error" => "Not Authorized"], 403);
    }

    public function verify(Request $request, $response, $args)
    {
        $params = $this->requireParams($request, ["hash"]);
        $token = trim($params["hash"]);
        $filter = [
            'AND' => [
                "token" => $token,
                "type" => Token::TYPE_EMAIL_VERIFICATION,
            ]
        ];
        if (!Token::exists($filter)) {
            throw new Http403();
        }
        $token = Token::load($filter)[0];
        if (!\App\Model\User::exists(["id" => $token->getUserId()])) {
            throw new Http403();
        }
        $user = \App\Model\User::load(["id" => $token->getUserId()])[0];
        $user->setState(\App\Model\User::STATE_CONFIRMED);
        $user->save();
        $token->delete();

        return $this->container->view->render(
            $response,
            ["OK"],
            200
        );
    }

    public function check(Request $request, $response, $args)
    {
        return $this->container->view->render($response, $_SESSION, 200);
    }

}
