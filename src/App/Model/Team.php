<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;
use App\Common;

class Team extends \App\Model
{
    protected static $fields = ["id", "name", "founded_at", "city", "www", "email"];

    public static function create($name, $city = null, $www = null, $email = null, $foundedAt = null)
    {
        $i = new self();
        $i->setName($name);
        $i->setCity($city);
        $i->setWww($www);
        $i->setEmail($email);
        $i->setFoundedAt($foundedAt);

        return $i;
    }

    protected function onSaveValidation()
    {
        if ($this->isNew() && self::exists([
            "name" => $this->getName()
        ])) {
            throw new Duplicate("Team already exists");
        }
    }

    public static function getFee($seasonId, $teamId = null) {
        $teamCondition = "";

        if ($teamId) {
            $teamCondition = "and htm.id = " . (int)$teamId;
        }

        $tournament_fee_query = "
            SELECT * FROM tournament_fee
            FROM season s
            LEFT JOIN tournament t ON s.id = t.season_id
            LEFT JOIN tournament_belongs_to_league_and_division tld ON tld.tournament_id = t.id
            LEFT JOIN fee_needed_for_league ffl ON ffl.league_id = tld.league_id AND ffl.since_season <= s.id
            LEFT JOIN fee f ON f.id = ffl.fee_id
            WHERE ffl.id = (
                SELECT ffl.id FROM fee_needed_for_league ffl
                LEFT JOIN season s2 on s2.id = ffl.since_season
                WHERE since_season <= s.id
                ORDER BY s2.start DESC LIMIT 1)
        ";

        // $data = \App\Context::getContainer()->db->query($query);

        $query = "select DISTINCT p.id as player_id, CONCAT(p.first_name, ' ', p.last_name) as player, group_concat(distinct tm.name separator '|') as team, f.amount, htm.name as home_team, htm.id as home_team_id
        from tournament t
        left join season s on s.id = t.season_id
        left join tournament_belongs_to_league_and_division tld ON tld.tournament_id = t.id
        left join roster r on r.tournament_belongs_to_league_and_division_id = tld.id
        left join team tm on tm.id = r.team_id
        left join player_at_roster pr on pr.roster_id = r.id
        left join player p on p.id = pr.player_id
        left join league l on tld.league_id = l.id
        left join fee_needed_for_league ffl on ffl.league_id = l.id
        left join fee f on f.id = ffl.fee_id
        left join player_fee_change pfc on pfc.player_id = pr.player_id and pfc.season_id = t.season_id
        left join player_at_team pt on pt.player_id = p.id
        left join team htm on htm.id = pt.team_id
        where t.season_id = " . (int)$seasonId . "
        and pt.first_season < s.id and pt.first_season >= (SELECT max(first_season) from player_at_team where player_id = p.id and first_season < s.id)
        " . $teamCondition . "
        and ffl.id = (select max(id) from fee_needed_for_league fl where fl.since_season < s.id)
        group by p.id, f.id, htm.id
        order by htm.id asc";

        $data = \App\Context::getContainer()->db->query($query);

        $data = $data->fetchAll();

        $out = [];
        $players = [];

        foreach ($data as $row) {
            $playerId = (int) $row['player_id'];
            if (!isset($out[$row['home_team']])) {
                $out[$row['home_team']] = [
                    "fee" => 0,
                    "players" => [],
                    "id" => $row['home_team_id'],
                ];
            }
            $out[$row['home_team']]["fee"] += (int)$row['amount'];
            $out[$row['home_team']]['players'][] = $row['player'];

            if (!isset($players[$playerId])) {
                $players[$playerId] = [
                    "id" => $playerId,
                    "name" => $row['player'],
                    "teams" => []
                ];
            }
            $players[$playerId]["teams"] = explode("|", $row["team"]);
        }

        $duplicatePlayers = array_filter($players, function ($teams) {
            return count($teams["teams"]) > 1;
        });

        return [
            'fee' => $out,
            'duplicate_players' => $duplicatePlayers
        ];
    }
}
