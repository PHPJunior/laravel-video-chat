<?php

namespace PhpJunior\LaravelVideoChat\Models\Conversation\Relationship;

use PhpJunior\LaravelVideoChat\Models\File\File;
use PhpJunior\LaravelVideoChat\Models\Message\Message;

/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/17/17
 * Time: 10:31 AM.
 */
trait ConversationRelationship
{
    /**
     * @return mixed
     */
    public function messages()
    {
        return $this->morphMany(Message::class, 'conversation');
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $this->morphMany(File::class, 'conversation');
    }

    /**
     * @return mixed
     */
    public function firstUser()
    {
        return $this->belongsTo(config('laravel-video-chat.user.model'), 'first_user_id');
    }

    /**
     * @return mixed
     */
    public function secondUser()
    {
        return $this->belongsTo(config('laravel-video-chat.user.model'), 'second_user_id');
    }
}
