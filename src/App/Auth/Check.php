<?php

namespace App\Auth;
use Slim\Http\Request;

class Check
{
    const ALLOW_ALL   = 'allow_all';
    const ALLOW_IP    = 'allow_ip';
    const ALLOW_TOKEN = 'allow_token';
    const ALLOW_NOONE = 'allow_noone';
    const ALLOW_LOCALHOST = 'allow_site_admin';

    private static $verifications = [
        self::ALLOW_ALL   => "authAllowAll",
        self::ALLOW_IP    => "authAllowIp",
        self::ALLOW_TOKEN => "authAllowToken",
        self::ALLOW_NOONE => "authAllowNoone",
        self::ALLOW_LOCALHOST => "authAllowLocalhost",
    ];

    public static function verify($type, $request, $response, $args)
    {
        if (!array_key_exists($type, self::$verifications)) {
            return false;
        }
        $method = self::$verifications[$type];
        if (!method_exists(__CLASS__, $method)) {
            return false;
        }
        return call_user_func_array([__CLASS__, $method], [$request, $response, $args]);
    }

    private static function authAllowAll($request, $response, $args)
    {
        return true;
    }

    private static function authAllowIp($request, $response, $args)
    {
        return true;
    }

    private static function authAllowToken($request, $response, $args)
    {
        return true;
    }

    private static function authAllowNoone($request, $response, $args)
    {
        return false;
    }

    private static function authAllowLocalhost(\Slim\Http\Request $request, $response, $args)
    {
        return $request->getAttribute('ip_address') == '127.0.0.1';
    }
}
