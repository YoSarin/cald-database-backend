<?php
namespace App\Controller;

use App\Model\Tournament;
use App\Model\PlayerAtTeam;
use App\Model\PlayerAtRoster;
use App\Model\UserHasPrivilege;
use App\Model\TournamentBelongsToLeagueAndDivision;
use App\Model\User as UserModel;
use Respect\Validation\Validator;

class Player extends \App\Common
{
    public function create(\Slim\Http\Request $request, $response, $args)
    {
        $request->requireParams(["first_name", "last_name", "birth_date", "sex"]);

        $firstName = trim($request->getParam("first_name"));
        $lastName = trim($request->getParam("last_name"));
        $birthDate = trim($request->getParam("birth_date"));
        $sex = trim($request->getParam("sex"));
        $email = trim($request->getParam("email"));
        $phone = trim($request->getParam("phone"));

        $user = UserModel::loggedUser($request->getToken());

        $p = \App\Model\Player::create($firstName, $lastName, $sex, $email, $birthDate);
        if (!empty($email)) {
            Validator::email()->assert($email);
            $p->setEmail($email);
        }
        if (!empty($phone)) {
            $p->setPhone($phone);
        }
        $p->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', "id" => $p->getId()],
            200
        );
    }

    public function update(\Slim\Http\Request $request, $response, $args)
    {
        $playerId = $request->requireParams(["player_id"]);

        $firstName = trim($request->getParam("first_name"));
        $lastName = trim($request->getParam("last_name"));
        $birthDate = trim($request->getParam("birth_date"));
        $sex = trim($request->getParam("sex"));
        $email = trim($request->getParam("email"));
        $phone = trim($request->getParam("phone"));

        $user = UserModel::loggedUser($request->getToken());

        $p = \App\Model\Player::loadById($playerId);
        if (!empty($email)) {
            Validator::email()->assert($email);
            $p->setEmail($email);
        }
        if (!empty($phone)) {
            $p->setPhone($phone);
        }
        if (!empty($sex)) {
            $p->setSex($sex);
        }
        if (!empty($birthDate)) {
            $p->setBirthDate($birthDate);
        }
        if (!empty($lastName)) {
            $p->setLastName($lastName);
        }
        if (!empty($firstName)) {
            $p->setFirstName($firstName);
        }

        $p->save();

        // Render index view
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player updated', "data" => $p->getData()],
            200
        );
    }

    public function history(\Slim\Http\Request $request, $response, $args)
    {
        $playerId = $request->requireParams(["player_id"]);
        $player = \App\Model\Player::loadById($playerId);
        $seasons = \App\Model\Season::load();

        $data = [
            "player" => $player->getData(),
            // array_values to not have wrongly indexed array
            // array_filter to get rid of empty values
            "seasons" => array_values(array_filter(
                array_map(function ($season) use ($playerId) {
                    $homeTeams = \App\Model\Team::load(["AND" => ["player_at_team.player_id" => $playerId, "player_at_team.first_season[<=]" => $season->getId()], "ORDER" => ["player_at_team.first_season" => "DESC"]], 1, 0, [
                        "[><]player_at_team" => ["team.id" => "team_id"],
                    ], true);
                    if (!$homeTeams) {
                        return;
                    }
                    $rosters = \App\Model\Roster::load(["AND" => ["player_at_roster.player_id" => $playerId, "tournament.season_id" => $season->getId()]], null, 0, [
                        "[><]tournament_belongs_to_league_and_division(tournament_belongs_to_league_and_division)" => ["roster.tournament_belongs_to_league_and_division_id" => "id"],
                        "[><]tournament(tournament)" => ["tournament_belongs_to_league_and_division.tournament_id" => "id"],
                        "[><]player_at_roster(player_at_roster)" => ["roster.id" => "roster_id"],
                        "[><]team(team)" => ["roster.team_id" => "id"],
                    ]);

                    return [
                        "season" => $season->getData(),
                        "home_teams" => array_map(function ($team) { return $team->getData(); }, $homeTeams),
                        "tournaments" => array_map(function($roster) use ($playerId) {
                            $tld = \App\Model\TournamentBelongsToLeagueAndDivision::loadById($roster->getTournamentBelongsToLeagueAndDivisionId());
                            $tournament = \App\Model\Tournament::loadById($tld->getTournamentId());
                            $team = \App\Model\Team::loadById($roster->getTeamId());
                            return [
                                "tournament" => $tournament->getData(),
                                "team" => $team->getData(),
                            ];
                        }, $rosters),
                    ];
                }, $seasons),
                function ($item) { return $item != null; }
            ))
        ];
        return $this->container->view->render(
            $response,
            $data,
            200
        );

        /*
        $teamIds = array_merge($teamMemberIds, $teamPlayerIds);
        $seasonIds = array_merge($seasonMemberIds, $seasonPlayerIds);
        $teams = \App\Model\Team::load(["id" => $teamIds], null, 0, [], true);
        $seasons = \App\Model\Season::load(["id" => $seasonIds], null, 0, [], true);
        */
    }

    public function listAll(\Slim\Http\Request $request, $response, $args)
    {
        $data = \App\Model\Player::load();
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => array_map( function ($item) {
                return $item->getData();
            }, $data)],
            200
        );
    }
}
