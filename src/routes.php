<?php
// Routes

$auth = new App\Auth($container);

$devController = new App\Controller\Developer($container);
$adminController = new App\Controller\Admin($container);
$seasonController = new App\Controller\Season($container);
$userController = new App\Controller\User($container);
$teamController = new App\Controller\Team($container);
$playerController = new App\Controller\Player($container);
$listController = new App\Controller\ListItems($container);
$rosterController = new App\Controller\Roster($container);

$testController = new App\Controller\Test($container);

$app->post('/developer/create', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "create"]));
$app->post('/developer/drop', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "drop"]));
$app->get('/healthcheck', $auth->verify(App\Auth\Check::ALLOW_ALL, [$devController, "healthcheck"]));

$app->post('/admin/tournament', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "createTournament"]));
$app->put('/admin/tournament/{id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "updateTournament"]));
$app->delete('/admin/tournament/{id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "deleteTournament"]));
$app->post('/admin/fee/pardon', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "pardonFee"]));
$app->delete('/admin/fee/pardon', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "cancelPardonFee"]));
$app->get('/admin/fee', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "getFee"]));
$app->put('/admin/user/{user_id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "updateUser"]));
$app->post('/admin/nationality', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "addNationality"]));
$app->put('/admin/nationality/{nationality_id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "updateNationality"]));
$app->delete('/admin/nationality/{nationality_id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$adminController, "deleteNationality"]));

$app->put('/season', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$seasonController, "createSeason"]));
$app->post('/season/{season_id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$seasonController, "updateSeason"]));
$app->put('/fee', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$seasonController, "createFee"]));
$app->post('/fee/{id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$seasonController, "updateFee"]));
$app->delete('/fee/{id}', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$seasonController, "deleteFee"]));
$app->put('/fee/{fee_id}/activate', $auth->verify(App\Auth\Check::ALLOW_ADMIN, [$seasonController, "activateFee"]));

$app->post('/user', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "create"]));
$app->post('/user/login', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "login"]));
$app->get('/user/verify/{hash}', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "verify"]));
$app->get('/user/me', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$userController, "getCurrent"]));
$app->put('/user/me', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$userController, "updateCurrent"]));

$app->get('/list/{type}', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$listController, "listAll"]));

$app->post('/team', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$teamController, "create"]));
$app->post('/team/{team_id}/user/{user_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$teamController, "addUserPriviledge"]));
$app->delete('/team/{team_id}/user/{user_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$teamController, "removeUserPriviledge"]));
$app->post('/team/{team_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$teamController, "update"]));
$app->post('/team/{team_id}/player/{player_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$teamController, "addPlayer"]));
$app->delete('/team/{team_id}/player/{player_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$teamController, "removePlayer"]));
$app->get('/team/{team_id}/season/{season_id}/fee', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$teamController, "getFee"]));

$app->get('/player/{player_id}/history', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$playerController, "history"]));
$app->post('/player', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$playerController, "create"]));
$app->post('/player/{player_id}', $auth->verify(App\Auth\Check::ALLOW_PLAYER_EDIT, [$playerController, "update"]));
$app->post('/player/{player_id}/address', $auth->verify(App\Auth\Check::ALLOW_PLAYER_EDIT, [$playerController, "addAddress"]));
$app->get('/player/{player_id}/address', $auth->verify(App\Auth\Check::ALLOW_PLAYER_EDIT, [$playerController, "getAddress"]));
$app->post('/player/{player_id}/address/{address_id}', $auth->verify(App\Auth\Check::ALLOW_PLAYER_EDIT, [$playerController, "updateAddress"]));
$app->delete('/player/{player_id}/address/{address_id}', $auth->verify(App\Auth\Check::ALLOW_PLAYER_EDIT, [$playerController, "deleteAddress"]));

$app->post('/roster', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$rosterController, "create"]));
$app->delete('/roster/{roster_id}', $auth->verify(App\Auth\Check::ALLOW_ROSTER_EDIT, [$rosterController, "remove"]));
$app->post('/roster/{roster_id}/player/{player_id}', $auth->verify(App\Auth\Check::ALLOW_ROSTER_EDIT, [$rosterController, "addPlayer"]));
$app->delete('/roster/{roster_id}/player/{player_id}', $auth->verify(App\Auth\Check::ALLOW_ROSTER_EDIT, [$rosterController, "removePlayer"]));

// testing APIs - to be deleted at the end
$app->get('/test', $auth->verify(App\Auth\Check::ALLOW_ALL, [$testController, "test"]));
$app->get('/test/req', $auth->verify(App\Auth\Check::ALLOW_ALL, [$testController, "request"]));
$app->post('/test/t/{team_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$testController, "team"]));
$app->post('/test/hs/{highschool_id}', $auth->verify(App\Auth\Check::ALLOW_HIGHSCHOOL_VIEW, [$testController, "hs"]));

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
