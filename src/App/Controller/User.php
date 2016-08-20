<?php
namespace App\Controller;

use App\Common;
use Slim\Http\Request;

class User extends \App\Common
{
    public function newUser(\Slim\Http\Request $request, $response, $args)
    {
        $this->requireParams($request, ["email", "password"]);
        $database = $this->container->db;

        $email = trim($request->getParam("email"));
        $password = trim($request->getParam("password"));
        $salt = md5(md5(md5(rand()) . time()) . $email);
        $id = $database->insert("user", [
            "email"    => $email,
            "password" => $this->passwordHash($email, $password, $salt),
            "salt"     => $salt,
        ]);
        if ($database->error()[1] !== null) {
            $this->container->logger->error($database->error());
            throw new \App\Exception\Database();
        }

        // Render index view
        return $this->container->view->render($response, ['status' => 'OK', 'info' => 'User created', "id" => $id], 200);
    }

    public function login(\Slim\Http\Request $request, $response, $args)
    {
        $this->requireParams($request, ["email", "password"]);
        $email = trim($request->getParam("email"));
        $password = trim($request->getParam("password"));
        $database = $this->container->db;

        $result = $database->select("user", "*", ["email" => $email]);
        if ($database->error()[1] !== null) {
            $this->container->logger->error($database->error()[2]);
            throw new \App\Exception\Database();
        }
        if (count($result) >= 1) {
            $user = $result[0];

            if ($user["password"] == $this->passwordHash($user["email"], $password, $user["salt"])) {
                $_SESSION["user"] = ["id" => $user["id"], "email" => $user["email"]];
                return $this->container->view->render($response, ["token" => $this->createToken($user["id"]), "data" => $_SESSION], 200);
            }
        }
        return $this->container->view->render($response, ["error" => "Not Authorized"], 403);
    }

    public function check(\Slim\Http\Request $request, $response, $args)
    {
        return $this->container->view->render($response, $_SESSION, 200);
    }

    private function createToken($userId)
    {
        return session_id();
    }

    private function passwordHash($email, $password, $salt)
    {
        return hash("sha256", hash("sha256", hash("sha256", $password) . $salt) . $email);
    }
}
