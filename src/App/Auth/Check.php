<?php
namespace App\Auth;

use Slim\Http\Request;
use App\Model\PlayerAtTeam;
use App\Model\UserHasPrivilege;
use App\Context;

class Check
{
    const ALLOW_ALL   = 'ALLOW_ALL';
    const ALLOW_IP    = 'ALLOW_IP';
    const ALLOW_TOKEN = 'ALLOW_TOKEN';
    const ALLOW_NOONE = 'ALLOW_NOONE';
    const ALLOW_ADMIN = 'ALLOW_ADMIN';
    const ALLOW_LOCALHOST = 'ALLOW_LOCALHOST';
    const ALLOW_TEAM_VIEW = 'ALLOW_TEAM_VIEW';
    const ALLOW_TEAM_EDIT = 'ALLOW_TEAM_EDIT';
    const ALLOW_HIGHSCHOOL_VIEW = 'ALLOW_HIGHSCHOOL_VIEW';
    const ALLOW_HIGHSCHOOL_EDIT = 'ALLOW_HIGHSCHOOL_EDIT';
    const ALLOW_PLAYER_VIEW = 'ALLOW_PLAYER_VIEW';
    const ALLOW_PLAYER_EDIT = 'ALLOW_PLAYER_EDIT';
    const ALLOW_ROSTER_EDIT = 'ALLOW_ROSTER_EDIT';
    const ALLOW_TOURNAMENT_ORGANIZER = 'ALLOW_TOURNAMENT_ORGANIZER';

    private static $verifications = [
        self::ALLOW_ALL   => "authAllowAll",
        self::ALLOW_IP    => "authAllowIp",
        self::ALLOW_TOKEN => "authAllowToken",
        self::ALLOW_NOONE => "authAllowNoone",
        self::ALLOW_ADMIN => "authAllowAdmin",
        self::ALLOW_LOCALHOST => "authAllowLocalhost",
        self::ALLOW_TEAM_VIEW => 'authAllowTeamView',
        self::ALLOW_TEAM_EDIT => 'authAllowTeamEdit',
        self::ALLOW_HIGHSCHOOL_VIEW => 'authAllowHighSchoolView',
        self::ALLOW_HIGHSCHOOL_EDIT => 'authAllowHighSchoolEdit',
        self::ALLOW_PLAYER_VIEW => 'authAllowPlayerView',
        self::ALLOW_PLAYER_EDIT => 'authAllowPlayerEdit',
        self::ALLOW_ROSTER_EDIT => 'authAllowRosterEdit',
        self::ALLOW_TOURNAMENT_ORGANIZER => 'authAllowTournamentOrganizer',
    ];

    public static function verify($type, $request, $response, $args)
    {
        if (!array_key_exists($type, self::$verifications)) {
            return false;
        }
        $method = self::$verifications[$type];
        if (!method_exists(__CLASS__, $method)) {
            return false;
        }
        $loggedUser = \App\Model\User::loggedUser($request->getToken());
        if ($loggedUser && $loggedUser->isAdmin()) {
            return true;
        }
        return call_user_func_array([__CLASS__, $method], [$request, $response, $args]);
    }

    public static function authAllowToken($request, $response, $args)
    {
        $token = $request->getToken();

        if (empty($token)) {
            throw new \App\Exception\Http\Http400("Missing token parameter");
        }

        if (\App\Model\Token::count(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]) != 1) {
            return false;
        }

        return true;
    }

    private static function authAllowAdmin($request, $response, $args)
    {
        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }
        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_ADMIN
            ]
        ]);
    }

    private static function authAllowTeamView($request, $response, $args)
    {
        if (!isset($args["team_id"])) {
            throw new \App\Exception\Http\Http400("Missing team_id in url");
        }
        $teamId = $args["team_id"];

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                "entity_id"  => $teamId,
                "privilege" => [\App\Model\UserHasPrivilege::PRIVILEGE_EDIT, \App\Model\UserHasPrivilege::PRIVILEGE_VIEW]
            ]
        ]);
    }

    private static function authAllowTeamEdit($request, $response, $args)
    {
        list($teamId) = $request->requireParams(["team_id"]);
        if (empty($teamId)) {
            throw new \App\Exception\Http\Http400("Missing team_id in url");
        }

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                "entity_id"  => $teamId,
                "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_EDIT
            ]
        ]);
    }
    
    private static function authAllowTournamentOrganizer($request, $response, $args)
    {
        $ids = $request->requireAtLeastOne(["tournament_id", "roster_id"]);

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }
        
        $condition = ["tournament.id" => (int)$ids["tournament_id"]];
        $joins = ["[><]tournament" => ["entity_id" => "organizing_team_id"]];
        
        if (array_key_exists("roster_id", $ids)) {
            $condition = ["roster.id" => (int)$ids["roster_id"]];
            $joins["[><]tournament_belongs_to_league_and_division"] = ["tournament.id" => "tournament_id"];
            $joins["[><]roster"] = ["tournament_belongs_to_league_and_division.id" => "tournament_belongs_to_league_and_division_id"];
        }
        
        return \App\Model\UserHasPrivilege::exists(
            [
                "AND" => array_merge([
                    "user_id" => $request->currentUser()->getId(),
                    "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                    "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_EDIT,
                ], $condition)
            ],
            $joins
        );
    }

    private static function authAllowHighschoolView($request, $response, $args)
    {
        if (!isset($args["highschool_id"])) {
            throw new \App\Exception\Http\Http400("Missing highschool_id in url");
        }
        $teamId = $args["highschool_id"];

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_HIGHSCHOOL,
                "entity_id"  => $teamId,
                "privilege" => [\App\Model\UserHasPrivilege::PRIVILEGE_EDIT, \App\Model\UserHasPrivilege::PRIVILEGE_VIEW]
            ]
        ]);
    }

    private static function authAllowHighschoolEdit($request, $response, $args)
    {
        if (!isset($args["highschool_id"])) {
            throw new \App\Exception\Http\Http400("Missing highschool_id in url");
        }
        $teamId = $args["highschool_id"];

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_HIGHSCHOOL,
                "entity_id"  => $teamId,
                "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_EDIT,
            ]
        ]);
    }

    private static function authAllowPlayerView($request, $response, $args)
    {
        if (!isset($args["player_id"])) {
            throw new \App\Exception\Http\Http400("Missing player_id in url");
        }
        $playerId = $args["player_id"];

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        $p = \App\Model\PlayerAtTeam::load(["player_id" => $playerId]);
        if (count($p) < 1) {
            throw new \App\Exception\Http\Http404();
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                "entity_id"  => $p[0]->getTeamId(),
                "privilege" => [
                    \App\Model\UserHasPrivilege::PRIVILEGE_EDIT,
                    \App\Model\UserHasPrivilege::PRIVILEGE_VIEW,
                ]
            ]
        ]);
    }

    private static function authAllowPlayerEdit($request, $response, $args)
    {
        if (!isset($args["player_id"])) {
            throw new \App\Exception\Http\Http400("Missing player_id in url");
        }
        $playerId = $args["player_id"];

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        $p = \App\Model\PlayerAtTeam::load(["player_id" => $playerId]);
        if (count($p) < 1) {
            throw new \App\Exception\Http\Http404();
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                "entity_id"  => $p[0]->getTeamId(),
                "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_EDIT
            ]
        ]);
    }

    private static function authAllowRosterEdit($request, $response, $args)
    {
        if (!isset($args["roster_id"])) {
            throw new \App\Exception\Http\Http400("Missing roster_id in url");
        }
        $rosterId = $args["roster_id"];

        if (!static::authAllowToken($request, $response, $args)) {
            return false;
        }

        $token = $request->getToken();

        $t = \App\Model\Token::load(["token" => $token, "type" => \App\Model\Token::TYPE_LOGIN]);
        if (count($t) < 1) {
            return false;
        }

        $p = \App\Model\Roster::load(["id" => $rosterId]);
        if (count($p) < 1) {
            throw new \App\Exception\Http\Http404();
        }

        return \App\Model\UserHasPrivilege::exists([
            "AND" => [
                "user_id" => $t[0]->getUserId(),
                "entity" => \App\Model\UserHasPrivilege::ENTITY_TEAM,
                "entity_id"  => $p[0]->getTeamId(),
                "privilege" => \App\Model\UserHasPrivilege::PRIVILEGE_EDIT
            ]
        ]);
    }

    private static function authAllowAll($request, $response, $args)
    {
        return true;
    }

    private static function authAllowIp($request, $response, $args)
    {
        return true;
    }

    private static function authAllowNoone($request, $response, $args)
    {
        return false;
    }

    private static function authAllowLocalhost(\Slim\Http\Request $request, $response, $args)
    {
        return in_array($request->getAttribute('ip_address'), ['172.17.0.1', '127.0.0.1']);
    }
}
