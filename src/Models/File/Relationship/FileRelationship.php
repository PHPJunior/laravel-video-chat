<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/7/17
 * Time: 10:54 PM.
 */

namespace PhpJunior\LaravelVideoChat\Models\File\Relationship;

use PhpJunior\LaravelVideoChat\Models\Message\Message;

trait FileRelationship
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
    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    /**
     * @return mixed
     */
    public function sender()
    {
        return $this->belongsTo(config('laravel-video-chat.user.model'), 'user_id');
    }
}
