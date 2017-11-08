<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/8/17
 * Time: 7:48 AM.
 */

namespace PhpJunior\LaravelVideoChat\Models\File\Attribute;

trait FileAttribute
{
    public function getFileDetailsAttribute()
    {
        return get_file_details($this->name);
    }
}
