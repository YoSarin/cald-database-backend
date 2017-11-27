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

        $query = "
        select
        	pr.player_id, group_concat(distinct tm.name separator '|') as team_played,
            COALESCE(pfc.amount, (CASE f.type WHEN 'player_per_season' THEN f.amount ELSE sum(f.amount) END)) as amount,
            htm.name as home_team, htm.id home_team_id, CONCAT(p.first_name, ' ', p.last_name) as player
        from player p
        left join player_at_roster pr on pr.player_id = p.id
        left join roster r on r.id = pr.roster_id
        left join tournament_belongs_to_league_and_division tld on tld.id = r.tournament_belongs_to_league_and_division_id
        left join tournament t on t.id = tld.tournament_id
        left join team tm on tm.id = r.team_id
        left join (
        	select f.*, ffl.league_id
        	from fee f
        	left join fee_needed_for_league ffl ON ffl.fee_id = f.id
        	where ffl.since_season = (
        		select since_season from fee_needed_for_league where league_id = ffl.league_id and ffl.since_season <= " . (int)$seasonId . " order by since_season desc limit 1
        	)
        ) f ON f.league_id = tld.league_id
        left join (
        	select t.id, t.name, pt.player_id
        	from player_at_team pt
        	left join team t ON pt.team_id = t.id
        	where pt.id = (
        		select id from player_at_team where player_id = pt.player_id and first_season <= " . (int)$seasonId . " order by first_season desc limit 1
        	)
        ) htm ON htm.player_id = pr.player_id
        left join player_fee_change pfc on pfc.player_id = p.id AND pfc.season_id = t.season_id
        where t.season_id = " . (int)$seasonId . "
        " . $teamCondition . "
        group by pr.player_id";

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
            $players[$playerId]["teams"] = explode("|", $row["team_played"]);
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
