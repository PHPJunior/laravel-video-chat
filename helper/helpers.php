<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/19/17
 * Time: 4:35 AM
 */

if (! function_exists('check')) {

    function check()
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if(auth()->guard($guard)->check()) return auth()->guard($guard);
        }
    }

}