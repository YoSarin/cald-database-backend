<?php
namespace App;

use Slim\Container;
use App\Exception\MissingParam;

abstract class Common
{
    /**
     * @var \Slim\Container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    protected function render(\Slim\Http\Response $response, $data, $status)
    {
        $this->container->view->render($response, $data, $status);
    }

    protected function requireParams(Request $request, $names)
    {
        return $request->requireParams($names);
    }
}
