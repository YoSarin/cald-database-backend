<?php
namespace App\Controller;

use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;
use App\Exception\Http\Http400;

class ListItems extends \App\Common
{

    public static $listable = [
        "player", "team", "player_at_team",
        "roster", "player_at_roster", "tournament",
        "season", "tournament_belongs_to_league_and_division",
        "division", "league", "user", "nationality", "fee", "fee_needed_for_league"
    ];

    public function listAll(\App\Request $request, $response, $args)
    {
        list($type) = $request->requireParams(["type"]);
        $type = strtolower($type);
        if (!in_array($type, self::$listable)) {
            throw new Http400("Item not listable");
        }
        $filter = null;
        $prefilter = $request->getParam("filter", null);
        $extend = (bool)$request->getParam("extend", false);

        $limit = $request->getParam("limit", null);
        $offset = $request->getParam("offset", null);

        if (!empty($prefilter)) {
            $filter = [];
            array_walk($prefilter, function ($value, $key) use (&$filter) {
                $filter[urldecode($key)] = $value;
            });
        }

        $model = "\\App\\Model\\" . ucfirst(\App\Model::camelcaseNotation($type));
        $joins = [];
        $data = $model::load($filter, $limit, $offset, $joins);
        $data = array_map(function ($item) use ($extend) {
            if ($extend) {
                return $item->getExtendedData();
            }
            return $item->getData();
        }, $data);

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => $data, 'filter' => $filter],
            200
        );
    }

    public function listCategory(\App\Request $request, $response, $args)
    {
        list($category) = $request->requireParams(["category"]);
        
        $category = strtolower($category);
        
        $year = $request->getParam("year", date("Y"));
        $gender = $request->getParam("gender", null);
        $maxInactiveSeasons = $request->getParam("max_inactive_seasons", 1);

        $data = \App\Model\Player::listPlayersInCategory($category, $year, $gender, $maxInactiveSeasons);

        \App\Context::getContainer()->logger->info($data[0]->firstName);

        return $this->container->view->render(
            $response,
            [
                'status' => 'OK',
                'data' => array_map(function ($person) { return $person->getData(); }, $data),
                "category" => $category,
                "year" => $year,
                "gender" => $gender,
                "max_inactive_seasons" => $maxInactiveSeasons
            ],
            200
        );
    }


}