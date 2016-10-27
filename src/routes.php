<?php
// Routes

$auth = new App\Auth($container);

$devController = new App\Controller\Developer($container);
$adminController = new App\Controller\Admin($container);
$userController = new App\Controller\User($container);
$teamController = new App\Controller\Team($container);
$listController = new App\Controller\ListItems($container);

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

$app->post('/user', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "create"]));
$app->post('/user/login', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "login"]));
$app->get('/user/verify/{hash}', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "verify"]));
$app->get('/user/me', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$userController, "getCurrent"]));
$app->put('/user/me', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$userController, "updateCurrent"]));

$app->get('/list/{type}', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$listController, "listAll"]));

$app->post('/team', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$teamController, "create"]));

// testing APIs - to be deleted at the end
$app->post('/test', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$testController, "test"]));
$app->get('/test/req', $auth->verify(App\Auth\Check::ALLOW_ALL, [$testController, "request"]));
$app->post('/test/t/{team_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$testController, "team"]));
$app->post('/test/hs/{highschool_id}', $auth->verify(App\Auth\Check::ALLOW_HIGHSCHOOL_VIEW, [$testController, "hs"]));

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
