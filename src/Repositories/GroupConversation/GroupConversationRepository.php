<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/21/17
 * Time: 11:27 PM
 */

namespace PhpJunior\LaravelVideoChat\Repositories\GroupConversation;

use PhpJunior\LaravelVideoChat\Events\NewGroupConversationMessage;
use PhpJunior\LaravelVideoChat\Models\Group\Conversation\GroupConversation;
use PhpJunior\LaravelVideoChat\Repositories\BaseRepository;

class GroupConversationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = GroupConversation::class;

    /**
     * @param $user
     * @return \Illuminate\Support\Collection
     */
    public function getAllGroupConversations($user)
    {
        $conversations = $this->query()->withCount('users')
            ->with([
                'messages' => function ($query) {
                    return $query->latest();
                } , 'users'
            ])->whereHas('users', function ($query) use ($user) {
                $query->where('id', $user);
            })->get();

        return $conversations;
    }

    /**
     * @param $groupConversationId
     * @param $channel
     * @return object
     */
    public function getGroupConversationMessageById($groupConversationId , $channel)
    {
        $conversation = $this->query()->with(['messages','messages.sender', 'users'])->find($groupConversationId);

        $collection = (object) null;
        $collection->group_conversation = $conversation;
        $collection->channel_name = $channel;
        $collection->users = $conversation->users;
        $collection->messages = $conversation->messages;

        return collect($collection);
    }

    /**
     * @param $groupName
     * @param array $users
     * @return bool
     */
    public function createGroupConversation($groupName , array $users)
    {
        $group = $this->query()->create([
            'name' => $groupName
        ]);

        if ($group){
            $group->users()->attach($users);
            return true;
        }

        return false;
    }

    /**
     * @param $groupConversationId
     * @param array $users
     * @return bool
     */
    public function removeMembersFromGroupConversation($groupConversationId , array $users)
    {
        $group = $this->find($groupConversationId);

        if ($group){
            $group->users()->detach($users);
            return true;
        }

        return false;
    }

    /**
     * @param $groupConversationId
     * @param $userId
     * @return bool
     */
    public function leaveFromGroupConversation($groupConversationId , $userId)
    {
        $group = $this->find($groupConversationId);

        if ($group){
            $group->users()->detach($userId);
            return true;
        }

        return false;
    }

    /**
     * @param $groupConversationId
     * @param array $users
     * @return bool
     */
    public function addMembersToExistingGroupConversation($groupConversationId , array $users)
    {
        $group = $this->find($groupConversationId);

        if ($group){
            $group->users()->attach($users);
            return true;
        }

        return false;
    }

    /**
     * @param $user
     * @param $groupConversationId
     * @return bool
     */
    public function canJoinGroupConversation($user , $groupConversationId)
    {
        $group = $this->find($groupConversationId);

        if ($group){
            foreach ($group->users()->get() as $member){
                if ($member->id == $user->id){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $userId
     * @param $groupConversationId
     * @return bool
     */
    public function checkUserExist($userId, $groupConversationId)
    {
        $group = $this->find($groupConversationId);

        if ($group){
            foreach ($group->users()->get() as $member){
                if ($member->id == $userId){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $groupConversationId
     * @param array $data
     * @return bool
     */
    public function sendGroupConversationMessage($groupConversationId, array $data)
    {
        $created = $this->find($groupConversationId)
            ->messages()
            ->create([
                'text' => $data['text'],
                'user_id' => $data['user_id']
            ]);

        if ($created){
            broadcast(new NewGroupConversationMessage( $data['text'] , $data['channel']));
            return true;
        }

        return false;
    }
}