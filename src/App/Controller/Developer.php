<?php
namespace App\Controller;

class Developer extends \App\Common
{
    public function install($request, $response, $args)
    {
        // Sample log message
        $this->container->logger->info("Slim-Skeleton '/check' route");
        // Initialize
        $database = $this->container->db;

        $commands = explode(';', file_get_contents('../data/create.sql'));
        foreach ($commands as $command) {
            $command = trim($command);
            if (empty($command)) {
                continue;
            }
            $database->query($command);
            if ($database->error()[1] !== null) {
                return $this->container->view->render($response, $database->error(), 500);
            }
        }

        // Render index view
        return $this->container->view->render($response, ['status' => 'OK', 'info' => 'database created'], 200);
    }


    public function drop($request, $response, $args)
    {
        // Render index view
        return $this->container->view->render($response, ['error' => 'Not implemented'], 404);
    }
}
