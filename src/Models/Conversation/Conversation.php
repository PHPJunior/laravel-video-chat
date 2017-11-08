<?php

namespace PhpJunior\LaravelVideoChat\Models\Conversation;

use Illuminate\Database\Eloquent\Model;
use PhpJunior\LaravelVideoChat\Models\Conversation\Relationship\ConversationRelationship;

class Conversation extends Model
{
    use ConversationRelationship;

    protected $table;

    protected $fillable = [
        'first_user_id', 'second_user_id', 'is_accepted',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.conversations_table');
    }
}
