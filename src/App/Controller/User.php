<?php
namespace App\Controller;

use App\Common;
use App\Exception\Database\Duplicate;
use App\Exception\Database;
use App\Exception\User\Unconfirmed;
use App\Exception\Http\Http403;
use App\Model\Token;
use App\Model\UserHasPrivilege;
use Slim\Http\Request;
use Respect\Validation\Validator;

class User extends \App\Common
{
    public function create(Request $request, $response, $args)
    {
        $request->requireParams(["email", "password", "login"]);

        $email = trim($request->getParam("email"));
        $password = trim($request->getParam("password"));
        $login = trim($request->getParam("login"));

        Validator::email()->assert($email);
        Validator::stringType()->length(6, null)->assert($password);

        $user = \App\Model\User::create($login, $password, $email);
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
        $request->requireParams(["login", "password"]);
        $login = trim($request->getParam("login"));
        $password = trim($request->getParam("password"));

        if (!\App\Model\User::exists(["login" => $login])) {
            throw new Http403();
        }

        $user = \App\Model\User::load(["login" => $login])[0];
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
        $params = $request->requireParams(["hash"]);
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

    public function getCurrent(Request $request, $response, $args)
    {
        $data = [
            'user' => $request->currentUser()->getData(),
            'rights' => array_map(
                function ($item) {
                    return $item->privilegeToString();
                },
                UserHasPrivilege::load(["user_id" => $request->currentUser()->getId()])
            )
        ];
        return $this->container->view->render(
            $response,
            ["status" => "OK", "data" => $data],
            200
        );
    }

    public function updateCurrent(Request $request, $response, $args)
    {
        $u = $request->currentUser();

        $login    = trim($request->getParam("login"));
        $password = trim($request->getParam("password"));
        $email    = trim($request->getParam("email"));

        if ($login) {
            $u->setLogin($login);
        }
        if ($email) {
            $u->setEmail($email);
        }
        if ($password) {
            $u->setPassword($password);
        }

        $u->save();

        return $this->container->view->render(
            $response,
            ["status" => "OK", "data" => $request->currentUser()->getData()],
            200
        );
    }
}
