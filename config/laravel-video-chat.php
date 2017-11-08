<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/16/17
 * Time: 1:51 PM.
 */

return [
    'relation' => [
        'conversations'       => PhpJunior\LaravelVideoChat\Models\Conversation\Conversation::class,
        'group_conversations' => PhpJunior\LaravelVideoChat\Models\Group\Conversation\GroupConversation::class,
    ],
    'user' => [
        'model' => App\User::class,
        'table' => 'users', // Existing user table name
    ],
    'table' => [
        'conversations_table'       => 'conversations',
        'messages_table'            => 'messages',
        'group_conversations_table' => 'group_conversations',
        'group_users_table'         => 'group_users',
        'files_table'               => 'files',
    ],
    'channel' => [
        'new_conversation_created' => 'new-conversation-created',
        'chat_room'                => 'chat-room',
        'group_chat_room'          => 'group-chat-room',
    ],
    'upload' => [
        'storage' => 'public',
    ],
];
