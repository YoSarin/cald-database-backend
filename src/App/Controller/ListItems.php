<?php
namespace App\Controller;

use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;
use App\Exception\Http\Http400;

class ListItems extends \App\Common
{

    public static $listable = [
        "team", "player", "tournament", "roster", "season", "player_at_team"
    ];

    public function listAll(\Slim\Http\Request $request, $response, $args)
    {
        list($type) = $this->requireParams($request, ["type"]);
        $type = strtolower($type);
        if (!in_array($type, self::$listable)) {
            throw new Http400("Item not listable");
        }
        $filter = null;
        $prefilter = $request->getParam("filter", null);
        $extend = (bool)$request->getParam("extend", false);
        if (!empty($prefilter)) {
            $filter = [];
            array_walk($prefilter, function ($value, $key) use (&$filter) {
                $filter[urldecode($key)] = $value;
            });
        }

        $model = "\\App\\Model\\" . ucfirst(\App\Model::camelcaseNotation($type));
        $data = $model::load($filter);

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => array_map(function ($item) use ($extend) {
                if ($extend) {
                    return $item->getExtendedData();
                } else {
                    return $item->getData();
                }
            }, $data), 'filter' => $filter],
            200
        );
    }
}
