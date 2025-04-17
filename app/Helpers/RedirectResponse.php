<?php
namespace App\Helpers;

class RedirectResponse
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
    public static function redirectWithMessage($route,  $param = null, $status = null, $message = null)
    {
        $message = $message ?? $status;
        return redirect()->route($route, $param)->with($status, $message);
    }

    public static function viewWithMessage($view, $data = [], $status = self::SUCCESS, $message = null)
    {
        return view($view, $data)->with($status, $message);
    }
}
