<?php
namespace App\Controller;

class Developer extends \App\Common
{
    public function create($request, $response, $args)
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

        $database = $this->container->db;
        $database->query("
            SET FOREIGN_KEY_CHECKS = 0;
            DROP TABLE fee;
            DROP TABLE highschool;
            DROP TABLE league;
            DROP TABLE player;
            DROP TABLE player_at_highschool;
            DROP TABLE player_at_roster;
            DROP TABLE player_at_team;
            DROP TABLE roster;
            DROP TABLE season;
            DROP TABLE team;
            DROP TABLE team_representative;
            DROP TABLE tournament;
            DROP TABLE user;
            DROP TABLE user_has_privilege;
            DROP TABLE token;
            SET FOREIGN_KEY_CHECKS = 1;
        ");
        if ($database->error()[1] !== null) {
            return $this->container->view->render($response, $database->error(), 500);
        }

        // Render index view
        return $this->container->view->render($response, ['status' => 'OK', 'info' => 'database dropped'], 200);
    }
}
