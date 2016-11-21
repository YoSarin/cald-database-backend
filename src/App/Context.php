<?php
namespace App;

class Context
{
    /**
     * @var $container \Slim\App
     */
    private static $app;

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
}
