<?php
namespace App;

use Slim\Container;
use App\Exception\MissingParam;

class ErrorHandler extends Common
{
    public function handle($request, $response, $exception)
    {
        switch (true) {
            case $exception instanceof Exception\MissingParam:
                return $this->container->view->render($response, ["error" => $exception->getMessage()], 400);
            default:
                return $this->container->view->render($response, ["error" => $exception->getMessage()], 500);
        }
    }
}
