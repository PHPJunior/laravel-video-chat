<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/19/17
 * Time: 4:35 AM.
 */
if (!function_exists('check')) {
    function check()
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard);
            }
        }
    }
}

if (!function_exists('human_file_size')) {
    function human_file_size($bytes, $decimals = 2)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }
}

if (!function_exists('get_file_details')) {
    function get_file_details($path)
    {
        return app('upload.manager')->fileDetails($path);
    }
}
