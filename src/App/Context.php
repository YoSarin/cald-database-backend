<?php
namespace App;

class Context
{
    /**
     * @var $container \Slim\App
     */
    private static $app;

    /**
     * @var $currentUser \App\Model\User
     */
    private static $currentUser;

    public static function setApp(\Slim\App $app)
    {
        self::$app = $app;
    }

    /**
     * @return \Slim\Container
     */
    public static function getContainer()
    {
        return self::$app->getContainer();
    }

    public static function logger()
    {
        return self::$app->getContainer()->logger;
    }

    public static function setUser(\App\Model\User $user) {
        self::$currentUser = $user;
    }

    /**
     * @return \App\Model\User
     */
    public static function currentUser()
    {
        return self::$currentUser;
    }
}
