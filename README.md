# Laravel Video Chat
Laravel Video Chat using Socket.IO and WebRTC

[![Build Status](https://travis-ci.org/PHPJunior/laravel-video-chat.svg?branch=master)](https://travis-ci.org/PHPJunior/laravel-video-chat)
[![StyleCI](https://styleci.io/repos/107812103/shield?branch=master)](https://styleci.io/repos/107812103)
[![Latest Stable Version](https://poser.pugx.org/php-junior/laravel-video-chat/v/stable)](https://packagist.org/packages/php-junior/laravel-video-chat)
[![Total Downloads](https://poser.pugx.org/php-junior/laravel-video-chat/downloads)](https://packagist.org/packages/php-junior/laravel-video-chat)

## Installation
```php
composer require php-junior/laravel-video-chat
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
PhpJunior\LaravelVideoChat\LaravelVideoChatServiceProvider::class,
```

```php 
php artisan vendor:publish --provider="PhpJunior\LaravelVideoChat\LaravelVideoChatServiceProvider"
```

And 
```php 
php artisan migrate
php artisan storage:link

change APP_URL in .env
```

This is the contents of the published config file:

```php
return [
    'relation'  => [
        'conversations' =>  PhpJunior\LaravelVideoChat\Models\Conversation\Conversation::class,
        'group_conversations' => PhpJunior\LaravelVideoChat\Models\Group\Conversation\GroupConversation::class
    ],
    'user' => [
        'model' =>  App\User::class,
        'table' =>  'users' // Existing user table name
    ],
    'table' => [
        'conversations_table'   =>  'conversations',
        'messages_table'        =>  'messages',
        'group_conversations_table' =>  'group_conversations',
        'group_users_table'     =>  'group_users',
        'files_table'           =>  'files'
    ],
    'channel'   =>  [
        'new_conversation_created'  =>  'new-conversation-created',
        'chat_room'                 =>  'chat-room',
        'group_chat_room'           =>  'group-chat-room'
    ],
    'upload' => [
        'storage' => 'public'
    ]
];
```

Uncomment `App\Providers\BroadcastServiceProvider` in the providers array of your `config/app.php` configuration file

Install the JavaScript dependencies:
```javascript
    npm install
    npm install --save laravel-echo js-cookie vue-timeago socket.io socket.io-client webrtc-adapter vue-chat-scroll
```

If you are running the Socket.IO server on the same domain as your web application, you may access the client library like 

```javascript
<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>
```

 in your application's `head` HTML element
 

Next, you will need to instantiate Echo with the `socket.io` connector and a `host`.

```vuejs
 require('webrtc-adapter');
 window.Cookies = require('js-cookie');
 
 import Echo from "laravel-echo"
 
 window.io = require('socket.io-client');
 
 window.Echo = new Echo({
     broadcaster: 'socket.io',
     host: window.location.hostname + ':6001'
 });
```

Finally, you will need to run a compatible Socket.IO server. Use
[tlaverdure/laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server) GitHub repository.


In `resources/assets/js/app.js` file:

```vuejs
 import VueChatScroll from 'vue-chat-scroll';
 import VueTimeago from 'vue-timeago';
 
 Vue.use(VueChatScroll);
 Vue.component('chat-room' , require('./components/laravel-video-chat/ChatRoom.vue'));
 Vue.component('group-chat-room', require('./components/laravel-video-chat/GroupChatRoom.vue'));
 Vue.component('video-section' , require('./components/laravel-video-chat/VideoSection.vue'));
 Vue.component('file-preview' , require('./components/laravel-video-chat/FilePreview.vue'));
 
 Vue.use(VueTimeago, {
     name: 'timeago', // component name, `timeago` by default
     locale: 'en-US',
     locales: {
         'en-US': require('vue-timeago/locales/en-US.json')
     }
 })
```

Run `npm run dev` to recompile your assets.

## Features

- One To One Chat ( With Video Call )
- Accept Message Request
- Group Chat
- File Sharing

## Usage

#### Get All Conversation and Group Conversation

```php
$groups = Chat::getAllGroupConversations();
$conversations = Chat::getAllConversations()
```

```blade
<ul class="list-group">
    @foreach($conversations as $conversation)
        <li class="list-group-item">
        @if($conversation->message->conversation->is_accepted)
            <a href="#">
                <h2>{{$conversation->user->name}}</h2>
                @if(!is_null($conversation->message))
                    <span>{{ substr($conversation->message->text, 0, 20)}}</span>
                @endif
            </a>
         @else
            <a href="#">
                <h2>{{$conversation->user->name}}</h2>
                @if($conversation->message->conversation->second_user_id == auth()->user()->id)
                    <a href="accept_request_route" class="btn btn-xs btn-success">
                        Accept Message Request
                    </a>
                @endif
            </a>
         @endif
        </li>
    @endforeach

    @foreach($groups as $group)
        <li class="list-group-item">
            <a href="#">
                <h2>{{$group->name}}</h2>
                <span>{{ $group->users_count }} Member</span>
            </a>
        </li>
    @endforeach
</ul>
```

#### Start Conversation 
```php
Chat::startConversationWith($otherUserId);
```

#### Accept Conversation 
```php
Chat::acceptMessageRequest($conversationId);
```

#### Get Conversation Messages

```php
$conversation = Chat::getConversationMessageById($conversationId);
```

```blade
<chat-room :conversation="{{ $conversation }}" :current-user="{{ auth()->user() }}"></chat-room>
```

#### Send Message

You can change message send route in component

```php
Chat::sendConversationMessage($conversationId, $message);
```

#### Start Video Call ( Not Avaliable On Group Chat )

You can change video call route . I defined video call route `trigger/{id}` method `POST`
Use `$request->all()` for video call.

```php
Chat::startVideoCall($conversationId , $request->all());
```

#### Start Group Conversation 
```php
Chat::createGroupConversation( $groupName , [ $otherUserId , $otherUserId2 ]);
```

#### Get Group Conversation Messages

```php
$conversation = Chat::getGroupConversationMessageById($groupConversationId);
```

```blade
<group-chat-room :conversation="{{ $conversation }}" :current-user="{{ auth()->user() }}"></group-chat-room>
```

#### Send Group Chat Message

You can change message send route in component

```php
Chat::sendGroupConversationMessage($groupConversationId, $message);
```

#### Add Members to Group

```php
Chat::addMembersToExistingGroupConversation($groupConversationId, [ $otherUserId , $otherUserId2 ])
```

#### Remove Members from Group

```php
Chat::removeMembersFromGroupConversation($groupConversationId, [ $otherUserId , $otherUserId2 ])
```

#### Leave From Group

```php
Chat::leaveFromGroupConversation($groupConversationId);
```

## File Sharing

Run this command `php artisan storage:link`

#### Send Files in Conversation

```php
Chat::sendFilesInConversation($conversationId , $request->file('files'));
```

#### Send Files in Group Conversation

```php
Chat::sendFilesInGroupConversation($groupConversationId , $request->file('files'));
```

## ToDo

- Add Members to Group
- Remove Member From Group

## Next Version

- Group Video Call

## Credits

- All Contributors

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[Demo Project](https://github.com/PHPJunior/laravel-video-chat-demo)

## Support on Beerpay
Hey dude! Help me out for a couple of :beers:!

[![Beerpay](https://beerpay.io/PHPJunior/laravel-video-chat/badge.svg?style=beer-square)](https://beerpay.io/PHPJunior/laravel-video-chat)  [![Beerpay](https://beerpay.io/PHPJunior/laravel-video-chat/make-wish.svg?style=flat-square)](https://beerpay.io/PHPJunior/laravel-video-chat?focus=wish)
