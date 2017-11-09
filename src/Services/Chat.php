<?php

namespace PhpJunior\LaravelVideoChat\Services;

use Illuminate\Contracts\Config\Repository;
use PhpJunior\LaravelVideoChat\Repositories\Conversation\ConversationRepository;
use PhpJunior\LaravelVideoChat\Repositories\GroupConversation\GroupConversationRepository;

class Chat
{
    protected $config;

    protected $conversation;

    protected $userId;
    /**
     * @var GroupConversationRepository
     */
    protected $group;

    /**
     * Chat constructor.
     *
     * @param Repository                  $config
     * @param ConversationRepository      $conversation
     * @param GroupConversationRepository $group
     */
    public function __construct(
        Repository $config,
        ConversationRepository $conversation,
        GroupConversationRepository $group
    ) {
        $this->config = $config;
        $this->conversation = $conversation;
        $this->userId = check() ? check()->user()->id : null;
        $this->group = $group;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllConversations()
    {
        return $this->conversation->getAllConversations($this->userId);
    }

    /**
     * @param $conversationId
     *
     * @return object
     */
    public function getConversationMessageById($conversationId)
    {
        if ($this->conversation->checkUserExist($this->userId, $conversationId)) {
            $channel = $this->getChannelName($conversationId, 'chat_room');

            return $this->conversation->getConversationMessageById($conversationId, $this->userId, $channel);
        }

        abort(404);
    }

    /**
     * @param $conversationId
     * @param $text
     */
    public function sendConversationMessage($conversationId, $text)
    {
        $this->conversation->sendConversationMessage($conversationId, [
            'text'    => $text,
            'user_id' => $this->userId,
            'channel' => $this->getChannelName($conversationId, 'chat_room'),
        ]);
    }

    /**
     * @param $conversationId
     * @param array $data
     */
    public function startVideoCall($conversationId, array $data)
    {
        $channel = $this->getChannelName($conversationId, 'chat_room');
        $this->conversation->startVideoCall($data, $channel);
    }

    /**
     * @param $userId
     */
    public function startConversationWith($userId)
    {
        $this->conversation->startConversationWith($this->userId, $userId);
    }

    /**
     * @param $conversationId
     */
    public function acceptMessageRequest($conversationId)
    {
        $this->conversation->acceptMessageRequest($this->userId, $conversationId);
    }

    /**
     * @param $conversationId
     * @param $type
     *
     * @return string
     */
    private function getChannelName($conversationId, $type)
    {
        return $this->config->get('laravel-video-chat.channel.'.$type).'-'.$conversationId;
    }

    /**
     * @param $groupName
     * @param array $users
     */
    public function createGroupConversation($groupName, array $users)
    {
        $users[] = $this->userId;
        $this->group->createGroupConversation($groupName, $users);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllGroupConversations()
    {
        return $this->group->getAllGroupConversations($this->userId);
    }

    /**
     * @param $groupConversationId
     *
     * @return object
     */
    public function getGroupConversationMessageById($groupConversationId)
    {
        if ($this->group->checkUserExist($this->userId, $groupConversationId)) {
            $channel = $this->getChannelName($groupConversationId, 'group_chat_room');

            return $this->group->getGroupConversationMessageById($groupConversationId, $channel);
        }

        abort(404);
    }

    /**
     * @param $groupConversationId
     * @param $text
     */
    public function sendGroupConversationMessage($groupConversationId, $text)
    {
        $this->group->sendGroupConversationMessage($groupConversationId, [
            'text'    => $text,
            'user_id' => $this->userId,
            'channel' => $this->getChannelName($groupConversationId, 'group_chat_room'),
        ]);
    }

    /**
     * @param $groupConversationId
     * @param array $users
     */
    public function removeMembersFromGroupConversation($groupConversationId, array $users)
    {
        $this->group->removeMembersFromGroupConversation($groupConversationId, $users);
    }

    /**
     * @param $groupConversationId
     * @param array $users
     */
    public function addMembersToExistingGroupConversation($groupConversationId, array $users)
    {
        $this->group->addMembersToExistingGroupConversation($groupConversationId, $users);
    }

    /**
     * @param $groupConversationId
     */
    public function leaveFromGroupConversation($groupConversationId)
    {
        $this->group->leaveFromGroupConversation($groupConversationId, $this->userId);
    }

    /**
     * @param $conversationId
     * @param $file
     */
    public function sendFilesInConversation($conversationId, $file)
    {
        $this->sendFiles($conversationId, $file, 'conversation');
    }

    /**
     * @param $groupConversationId
     * @param $file
     */
    public function sendFilesInGroupConversation($groupConversationId, $file)
    {
        $this->sendFiles($groupConversationId, $file, 'group');
    }

    private function sendFiles($id, $file, $type)
    {
        switch ($type) {
            case 'conversation':
                $this->conversation->sendFilesInConversation($id, [
                    'file'    => $file,
                    'text'    => 'File Sent',
                    'user_id' => $this->userId,
                    'channel' => $this->getChannelName($id, 'chat_room'),
                ]);
                break;
            case 'group':
                $this->group->sendFilesInConversation($id, [
                    'file'    => $file,
                    'text'    => 'File Sent',
                    'user_id' => $this->userId,
                    'channel' => $this->getChannelName($id, 'group_chat_room'),
                ]);
                break;
        }
    }
}
