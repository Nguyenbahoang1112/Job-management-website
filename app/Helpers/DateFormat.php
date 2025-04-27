<?php

namespace App\Helpers;
use Carbon\Carbon;

class DateFormat
{
    static function formatDate($date, $format = 'Y-m-d')
    {
        if (!$date) return null;

        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
    static function formatTime($time, $format = 'H:i')
    {
        if (!$time) return null;

        try {
            return Carbon::parse($time)->format($format);
        } catch (\Exception $e) {
            return $time;
        }
    }
    static function formatDateTime($date, $format = 'd/m/Y - H:i')
    {
        if (!$date) return null;

        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }

}
