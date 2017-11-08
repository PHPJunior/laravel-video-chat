<?php

namespace PhpJunior\LaravelVideoChat\Models\Group\Conversation;

use Illuminate\Database\Eloquent\Model;
use PhpJunior\LaravelVideoChat\Models\Group\Conversation\Relationship\GroupConversationRelationship;

class GroupConversation extends Model
{
    use GroupConversationRelationship;

    protected $table;

    protected $fillable = [
        'name',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.group_conversations_table');
    }
}
