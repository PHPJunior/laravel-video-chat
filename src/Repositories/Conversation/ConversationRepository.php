<?php

namespace PhpJunior\LaravelVideoChat\Repositories\Conversation;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use PhpJunior\LaravelVideoChat\Events\NewConversationMessage;
use PhpJunior\LaravelVideoChat\Events\VideoChatStart;
use PhpJunior\LaravelVideoChat\Models\Conversation\Conversation;
use PhpJunior\LaravelVideoChat\Repositories\BaseRepository;
use PhpJunior\LaravelVideoChat\Services\UploadManager;

class ConversationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Conversation::class;
    /**
     * @var UploadManager
     */
    private $manager;

    /**
     * ConversationRepository constructor.
     *
     * @param UploadManager $manager
     */
    public function __construct(UploadManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $user
     *
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
     *
     * @return bool
     */
    public function canJoinConversation($user, $conversation)
    {
        $thread = $this->find($conversation);

        if ($thread) {
            if (($thread->first_user_id == $user->id) || ($thread->second_user_id == $user->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $conversationId
     * @param $userID
     * @param $channel
     *
     * @return object
     */
    public function getConversationMessageById($conversationId, $userID, $channel)
    {
        $conversation = $this->query()->with(['messages', 'messages.sender', 'messages.files', 'firstUser', 'secondUser', 'files'])->find($conversationId);

        $collection = (object) null;
        $collection->conversationId = $conversationId;
        $collection->channel_name = $channel;
        $collection->user = ($conversation->firstUser->id == $userID) ? $conversation->secondUser : $conversation->firstUser;
        $collection->messages = $conversation->messages;
        $collection->files = $conversation->files;

        return collect($collection);
    }

    /**
     * @param $conversationId
     * @param array $data
     *
     * @return bool
     */
    public function sendConversationMessage($conversationId, array $data)
    {
        return $this->sendMessage($conversationId, $data);
    }

    /**
     * @param array $data
     * @param $channel
     */
    public function startVideoCall(array $data, $channel)
    {
        broadcast(new VideoChatStart($data, $channel));
    }

    /**
     * @param $firstUserId
     * @param $secondUserId
     *
     * @return bool
     */
    public function startConversationWith($firstUserId, $secondUserId)
    {
        $created = $this->query()->create([
            'first_user_id'  => $firstUserId,
            'second_user_id' => $secondUserId,
        ]);

        if ($created) {
            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @param $conversationId
     *
     * @return bool
     */
    public function acceptMessageRequest($userId, $conversationId)
    {
        if ($this->checkUserExist($userId, $conversationId)) {
            $conversation = $this->find($conversationId);
            $conversation->is_accepted = true;
            $conversation->save();

            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @param $conversationId
     *
     * @return bool
     */
    public function checkUserExist($userId, $conversationId)
    {
        $thread = $this->find($conversationId);

        if ($thread) {
            if (($thread->first_user_id == $userId) || ($thread->second_user_id == $userId)) {
                return true;
            }
        }

        return false;
    }

    public function sendFilesInConversation($conversationId, array $data)
    {
        return $this->sendMessage($conversationId, $data);
    }

    /**
     * @param $conversationId
     * @param array $data
     *
     * @return bool
     */
    private function sendMessage($conversationId, array $data)
    {
        $conversation = $this->find($conversationId);

        $created = $conversation->messages()
            ->create([
                'text'    => $data['text'],
                'user_id' => $data['user_id'],
            ]);

        if ($created) {
            if (array_key_exists('file', $data)) {
                foreach ($data['file'] as $file) {
                    $fileName = Carbon::now()->format('YmdHis').'-'.$file->getClientOriginalName();
                    $path = str_finish('', '/').$fileName;
                    $content = File::get($file->getRealPath());
                    $result = $this->manager->saveFile($path, $content);

                    if ($result === true) {
                        $conversation->files()->create([
                            'message_id' => $created->id,
                            'name'       => $fileName,
                            'user_id'    => $data['user_id'],
                        ]);
                    }
                }
            }

            $data['files'] = $conversation->messages()->find($created->id)->files()->get();

            broadcast(new NewConversationMessage($data['text'], $data['channel'], $data['files']));

            return true;
        }

        return false;
    }
}
