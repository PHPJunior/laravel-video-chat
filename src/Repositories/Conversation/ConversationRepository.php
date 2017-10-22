<?php

namespace PhpJunior\LaravelVideoChat\Repositories\Conversation;

use PhpJunior\LaravelVideoChat\Events\NewConversationMessage;
use PhpJunior\LaravelVideoChat\Events\VideoChatStart;
use PhpJunior\LaravelVideoChat\Models\Conversation\Conversation;
use PhpJunior\LaravelVideoChat\Repositories\BaseRepository;

class ConversationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Conversation::class;

    /**
     * @param $user
     * @return \Illuminate\Support\Collection
     */
    public function getAllConversations($user)
    {
        $conversations = $this->query()->with(['messages' => function ($query) {
            return $query->latest();
        }, 'firstUser', 'secondUser'])->where('first_user_id', $user)->orWhere('second_user_id', $user)->get();

        $threads = [];

        foreach ($conversations as $conversation) {
            $collection = (object) null;
            $collection->message = $conversation->messages->first();
            $collection->user = ($conversation->firstUser->id == $user) ? $conversation->secondUser : $conversation->firstUser;
            $threads[] = $collection;
        }

        return collect($threads);
    }

    /**
     * @param $user
     * @param $conversation
     * @return bool
     */
    public function canJoinConversation($user , $conversation)
    {
        $thread = $this->find($conversation);

        if ($thread){
            if (($thread->first_user_id == $user->id) || ($thread->second_user_id == $user->id)){
                return true;
            }
        }

        return false;
    }

    /**
     * @param $conversationId
     * @param $userID
     * @param $channel
     * @return object
     */
    public function getConversationMessageById($conversationId , $userID , $channel)
    {
        $conversation = $this->query()->with(['messages','messages.sender', 'firstUser', 'secondUser'])->find($conversationId);

        $collection = (object) null;
        $collection->conversationId = $conversationId;
        $collection->channel_name = $channel;
        $collection->user = ($conversation->firstUser->id == $userID) ? $conversation->secondUser : $conversation->firstUser;
        $collection->messages = $conversation->messages;

        return collect($collection);
    }

    /**
     * @param $conversationId
     * @param array $data
     * @return bool
     */
    public function sendConversationMessage($conversationId , array $data)
    {
        $created = $this->find($conversationId)
            ->messages()
            ->create([
                'text' => $data['text'],
                'user_id' => $data['user_id']
            ]);

        if ($created){
            broadcast(new NewConversationMessage( $data['text'] , $data['channel']));
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param $channel
     */
    public function startVideoCall(array $data , $channel)
    {
        broadcast(new VideoChatStart($data , $channel));
    }

    /**
     * @param $firstUserId
     * @param $secondUserId
     * @return bool
     */
    public function startConversationWith($firstUserId, $secondUserId)
    {
        $created = $this->query()->create([
            'first_user_id'     =>  $firstUserId,
            'second_user_id'    =>  $secondUserId
        ]);

        if ($created){
            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @param $conversationId
     * @return bool
     */
    public function checkUserExist($userId, $conversationId)
    {
        $thread = $this->find($conversationId);

        if ($thread){
            if (($thread->first_user_id == $userId) || ($thread->second_user_id == $userId)){
                return true;
            }
        }

        return false;
    }
}