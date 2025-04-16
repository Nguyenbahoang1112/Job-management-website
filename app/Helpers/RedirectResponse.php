<?php
namespace App\Helpers;

class RedirectResponse
{
    public static function redirect($route, $data = null, $message = null)
    {
        if ($data) {
            return redirect()->route($route)->with('data', $data);
        }

        if ($message) {
            return redirect()->route($route)->with('message', $message);
        }

        return redirect()->route($route);
    }
}
