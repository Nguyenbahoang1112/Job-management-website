<?php

namespace App\Helpers;

class RedirectResponse
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public static function success(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('success', $message);
    }

    public static function error(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('error', $message);
    }

    public static function warning(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('warning', $message);
    }

    public static function info(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('info', $message);
    }
}
