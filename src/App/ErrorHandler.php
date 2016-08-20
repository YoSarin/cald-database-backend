<?php
namespace App;

use Slim\Container;
use App\Exception\Database;
use App\Exception\MissingParam;

class ErrorHandler extends Common
{
    public function handle($request, $response, $exception)
    {
        switch (true) {
            case $exception instanceof Exception\MissingParam:
                return $this->container->view->render($response, ["error" => $exception->getMessage()], 400);
            case $exception instanceof Exception\Database:
                return $this->container->view->render($response, ["error" => "Database went off"], 500);
            default:
                return $this->container->view->render($response, ["error" => $exception->getMessage()], 500);
        }
    }
}
