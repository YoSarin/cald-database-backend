<?php
namespace App;

class Request extends \Slim\Http\Request
{
    public static function fromRequest(\Slim\Http\Request $r)
    {
        return new static(
            $r->originalMethod,
            $r->uri,
            $r->headers,
            $r->cookies,
            $r->serverParams,
            $r->body,
            $r->uploadedFiles = []
        );
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
                $output[] = $params[$name];
            }
        }

        if (!empty($missing)) {
            throw new \App\Exception\MissingParam("Mandatory params missing: " . implode(', ', $missing));
        }

        return $output;
    }

    public function type()
    {
        return ["OK"];
    }
}
