<?php
// Routes

$auth = new App\Auth($container);

$devController = new App\Controller\Developer($container);
$userController = new App\Controller\User($container);
$teamController = new App\Controller\Team($container);
$listController = new App\Controller\ListItems($container);

$testController = new App\Controller\Test($container);

$app->post('/developer/create', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "create"]));
$app->post('/developer/drop', $auth->verify(App\Auth\Check::ALLOW_LOCALHOST, [$devController, "drop"]));

$app->post('/user', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "create"]));
$app->post('/user/login', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "login"]));
$app->get('/user/verify/{hash}', $auth->verify(App\Auth\Check::ALLOW_ALL, [$userController, "verify"]));

$app->get('/list/{type}', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$listController, "listAll"]));

$app->post('/team', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$teamController, "create"]));

$app->post('/test', $auth->verify(App\Auth\Check::ALLOW_TOKEN, [$testController, "test"]));
$app->post('/test/t/{team_id}', $auth->verify(App\Auth\Check::ALLOW_TEAM_EDIT, [$testController, "team"]));
$app->post('/test/hs/{highschool_id}', $auth->verify(App\Auth\Check::ALLOW_HIGHSCHOOL_VIEW, [$testController, "hs"]));
