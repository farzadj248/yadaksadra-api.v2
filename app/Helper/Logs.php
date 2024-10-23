<?php

namespace App\Helper;
use Log;

class Logs
{
    public static function stack($channel,$levels,$error){
        // Logs::stack("admins","alert","showing all media for admins - farzad jafari");
        switch ($levels) {
            case 'emergency':
                Log::stack(['daily', $channel])->emergency($error);
                break;

            case 'alert':
                Log::stack(['daily', $channel])->alert($error);
                break;

            case 'critical':
                Log::stack(['daily', $channel])->critical($error);
                break;

            case 'error':
                Log::stack(['daily', $channel])->error($error);
                break;

            case 'warning':
                Log::stack(['daily', $channel])->warning($error);
                break;

            case 'notice':
                Log::stack(['daily', $channel])->notice($error);
                break;

            case 'info':
                Log::stack(['daily', $channel])->info($error);
                break;

            case 'debug':
                Log::stack(['daily', $channel])->debug($error);
                break;
        }
    }
}
