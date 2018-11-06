<?php
namespace App;

use Slim\Container;

class ErrorHandler extends Common
{
    public function handle($request, $response, $exception)
    {
        $data = ["error" => $exception->getMessage(), "type" => \App\Exception::getType($exception)];
        $code = 500;

        switch (true) {
            case $exception instanceof \App\Exception\Database:
                $data['error'] = "Database went off: " . $exception->getMessage();
                $code = $exception->getCode();
                $this->container->logger->error($exception->getMessage());
                break;
            case $exception instanceof \App\Exception\Http:
                $code = $exception->getCode();
                break;
            case $exception instanceof \Respect\Validation\Exceptions\AllOfException:
                $code = 400;
                $data["error"] = $exception->getFullMessage();
                break;
            default:
                $this->container->logger->error($exception->getMessage());
        }

        return $this->container->view->render($response, $data, $code);
    }
}
