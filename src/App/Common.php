<?php
namespace App;

use Slim\Container;
use Slim\Http\Request;
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
        $output = [];
        $params = array_merge(
            $request->getParams(),
            $request->getAttribute('route')->getArguments()
        );
        $missing = [];
        foreach ($names as $name) {
            if (!array_key_exists($name, $params)) {
                $missing[] = $name;
            } else {
                $output[] = $params[$name];
            }
        }

        if (!empty($missing)) {
            throw new \App\Exception\MissingParam("Mandatory params missing: " . implode(', ', $missing));
        }

        return $output;
    }
}
