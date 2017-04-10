<?php
namespace App\Controller;

use App\Model\Player;
use App\Model\Fee;
use App\Model\Season;
use App\Model\Tournament;
use App\Model\PlayerFeeChange;
use App\Model\FeeNeededForLeague;
use App\Model\TournamentBelongsToLeagueAndDivision;
use App\Exception\Http\Http404;
use App\Exception\WrongParam;
use App\Context;

class Admin extends \App\Common
{
    public function createTournament($request, $response, $args)
    {
        list($name, $date, $location, $duration, $season_id, $league_ids, $division_ids) = $request->requireParams(
            ["name", "date", "location", "duration", "season_id", "league_ids", "division_ids"]
        );

        $t = Tournament::create($name, $date, $location, $duration, $season_id);
        $t->save();
        $tldList = [];
        foreach ($league_ids as $league_id) {
            foreach ($division_ids as $division_id) {
                $tld = TournamentBelongsToLeagueAndDivision::create($t->getId(), $league_id, $division_id);
                $tld->save();
                $tldList[] = $tld->getExtendedData();
            }
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $tldList],
            200
        );
    }

    public function updateTournament($request, $response, $args)
    {
        list($id) = $request->requireParams(["id"]);

        $t = Tournament::loadById($id);
        $t->updateByRequest($request);
        $t->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $t->getExtendedData()],
            200
        );
    }

    public function deleteTournament($request, $response, $args)
    {
        list($id) = $request->requireParams(["id"]);

        $t = Tournament::loadById($id);
        $t->setDeleted(true);
        $t->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK'],
            200
        );
    }

    public function pardonFee($request, $response, $args)
    {
        list($playerId, $seasonId) = $request->requireParams(["player_id", "season_id"]);
        if (!Player::exists(["id" => $playerId])) {
            throw new Http404("player does not exist");
        }
        if (!Season::exists(["id" => $seasonId])) {
            throw new Http404("season does not exist");
        }

        $feeChange = PlayerFeeChange::create($playerId, $seasonId, 0);
        $feeChange->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $feeChange->getData()],
            200
        );
    }

    public function cancelPardonFee($request, $response, $args)
    {
        list($id) = $request->requireParams(["pardon_id"]);

        $feeChange = PlayerFeeChange::loadById($id);
        if (empty($feeChange)) {
            throw new Http404("this fee pardon does not exist");
        }

        $feeChange->delete();

        return $this->container->view->render(
            $response,
            ['status' => 'OK'],
            200
        );
    }

    public function getFee($request, $response, $args)
    {
        list($id) = $request->requireParams(["season_id"]);

        if (!Season::exists(['id' => $id])) {
            throw new Http404("No such season");
        }

        $data = Context::getContainer()->db->query(
            "select p.id as player_id, CONCAT(p.first_name, ' ', p.last_name) as player, tm.name as team, f.amount
            from tournament t
            left join season s on s.id = t.season_id
            left join tournament_belongs_to_league_and_division tld ON tld.tournament_id = t.id
            left join roster r on r.tournament_belongs_to_league_and_division_id = tld.id
            left join team tm on tm.id = r.team_id
            left join player_at_roster pr on pr.roster_id = r.id
            left join player p on p.id = pr.player_id
            left join league l on tld.league_id = l.id
            left join fee_needed_for_league ffl on ffl.league_id = l.id and ffl.valid and ffl.since <= s.start
            left join fee f on f.id = ffl.fee_id
            left join player_fee_change pfc on pfc.player_id = pr.player_id and pfc.season_id = t.season_id
            where t.season_id = " . (int)$id . "
            group by tm.id, p.id, f.id
            order by tm.id asc"
        )->fetchAll();

        $out = [];
        $players = [];

        foreach ($data as $row) {
            $playerId = (int) $row['player_id'];
            if (!isset($out[$row['team']])) {
                $out[$row['team']] = [
                    "fee" => 0,
                    "players" => [],
                ];
            }
            $out[$row['team']]["fee"] += (int)$row['amount'];
            $out[$row['team']]['players'][] = $row['player'];

            if (!isset($players[$playerId])) {
                $players[$playerId] = [
                    "id" => $playerId,
                    "name" => $row['player'],
                    "teams" => []
                ];
            }
            $players[$playerId]["teams"][] = $row["team"];
        }

        $duplicatePlayers = array_filter($players, function ($teams) {
            return count($teams["teams"]) > 1;
        });

        return $this->container->view->render(
            $response,
            [
                'status' => 'OK',
                'data' => [
                    'fee' => $out,
                    'duplicate_players' => $duplicatePlayers
                ]
            ],
            200
        );
    }

    public function updateUser(\App\Request $request, $response, $args)
    {
        list($id) = $request->requireParams(["user_id"]);
        $login    = trim($request->getParam("login"));
        $password = trim($request->getParam("password"));
        $email    = trim($request->getParam("email"));
        $state    = trim($request->getParam("state"));

        $u = \App\Model\User::load(["id" => (int)$id])[0];

        if ($login) {
            $u->setLogin($login);
        }
        if ($email) {
            $u->setEmail($email);
        }
        if ($password) {
            $u->setPassword($password);
        }
        if ($state && in_array($state, \App\Model\User::allowedStates())) {
            $u->setState($state);
        } else if ($state) {
            throw new WrongParam("State must be one of '" . implode("', '", \App\Model\User::allowedStates()) . "'");
        }

        $u->save();

        return $this->container->view->render(
            $response,
            ["status" => "OK", "data" => $u->getData()],
            200
        );
    }
}
