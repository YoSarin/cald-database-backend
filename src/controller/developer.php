<?php
namespace controller;

class Developer
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function install($request, $response, $args)
    {
        // Sample log message
        $this->container->logger->info("Slim-Skeleton '/check' route");
        // Initialize
        $database = new \medoo([
            'database_type' => 'mysql',
            'database_name' => 'cald',
            'server' => 'localhost',
            'username' => 'cald',
            'password' => 'cald',
            'charset' => 'utf8'
        ]);

        $commands = explode(';', file_get_contents('../data/create.sql'));
        foreach ($commands as $command) {
            $command = trim($command);
            if (empty($command)) {
                continue;
            }
            $database->query($command);
            if ($database->error()[1] !== null) {
                var_dump($command);
                var_dump($database->error());
                die();
            }
        }

        // Render index view
        return $this->container->renderer->render($response, 'index.phtml', $args);
    }
}
