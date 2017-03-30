<?php
namespace App;
use App\Auth\Check;
use App\Context;

class Auth extends Common
{
    private $type;
    private $callback;

    public function verify($type, $callback)
    {
        return [$this->withTypeAndCallback($type, $callback), 'verificationCallback'];
    }

    public function verificationCallback($request, $response, $args)
    {
        try {
            Context::setUser($request->currentUser());
        } catch (\Exception $e) {}
        if (Auth\Check::verify($this->type, $request, $response, $args)) {
            return call_user_func_array($this->callback, [$request, $response, $args]);
        }

        return $this->fail($request, $response, $args);
    }

    private function fail($request, $response, $args, $message = "Not authorized", $code = 403)
    {
        return $this->container->view->render($response, ["error" => $message], $code);
    }

    private function withTypeAndCallback($type, $callback)
    {
        $n = new self($this->container);
        $n->type = $type;
        $n->callback = $callback;
        return $n;
    }
}
