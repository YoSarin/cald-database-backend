<?php
namespace App;
use App\Model\User;
use App\Exception\Http\Http404;

class Request extends \Slim\Http\Request
{
    private $currentUser;

    public static function fromRequest(\Slim\Http\Request $r)
    {
        $i = new static(
            $r->originalMethod,
            $r->uri,
            $r->headers,
            $r->cookies,
            $r->serverParams,
            $r->body,
            $r->uploadedFiles = []
        );
        $i->currentUser = null;
        return $i;
    }

    public function requireParams($names)
    {
        $output = [];
        $params = array_merge(
            $this->getParams(),
            $this->getAttribute('route')->getArguments()
        );
        $missing = [];
        foreach ($names as $name) {
            if (!array_key_exists($name, $params)) {
                $missing[] = $name;
            } else {
                $output[$name] = $params[$name];
            }
        }

        if (!empty($missing)) {
            throw new \App\Exception\MissingParam("Mandatory params missing: " . implode(', ', $missing));
        }

        return $output;
    }

    public function currentUser()
    {
        if (!$this->currentUser) {
            list($token) = $this->requireParams(['token']);
            $tokens = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
            if (empty($tokens)) {
                throw new Http404("Not found");
            }
            $this->currentUser = \App\Model\User::load(["id" => $tokens[0]->getUserId()])[0];
        }
        return $this->currentUser;
    }

    public function getToken()
    {
        $token = $this->getParam('token');
        if (empty($token)) {
            $token = $this->headers->get('X-Auth-Token');
        }

        return $token;
    }
}
