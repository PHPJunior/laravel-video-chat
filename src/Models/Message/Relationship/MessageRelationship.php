<?php

namespace PhpJunior\LaravelVideoChat\Models\Message\Relationship;

use PhpJunior\LaravelVideoChat\Models\File\File;

/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/17/17
 * Time: 3:18 PM.
 */
trait MessageRelationship
{
    /**
     * @return mixed
     */
    public function conversation()
    {
        return $this->morphTo();
    }

    /**
     * @return mixed
     */
    public function sender()
    {
        return $this->belongsTo(config('laravel-video-chat.user.model'), 'user_id');
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $this->hasMany(File::class, 'message_id');
    }
}
