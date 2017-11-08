<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-comment"></span>{{ conversation.group_conversation.name }}

                        <button class="btn btn-danger btn-sm pull-right" @click="leaveFromGroup" type="button">
                            Leave
                        </button>
                    </div>
                    <div class="panel-body">
                        <ul class="chat" v-chat-scroll>
                            <li class="clearfix" v-for="message in messages" v-bind:class="{ 'right' : check(message.sender.id), 'left' : !check(message.sender.id) }">
                            <span class="chat-img" v-bind:class="{ 'pull-right' : check(message.sender.id) , 'pull-left' : !check(message.sender.id) }">
                                <img :src="'http://placehold.it/50/FA6F57/fff&text='+ message.sender.name" alt="User Avatar" class="img-circle" />
                            </span>
                                <div class="chat-body clearfix">
                                    <div class="header">
                                        <small class=" text-muted"><span class="glyphicon glyphicon-time"></span><timeago :since="message.created_at" :auto-update="10"></timeago></small>
                                        <strong v-bind:class="{ 'pull-right' : check(message.sender.id) , 'pull-left' : !check(message.sender.id)}" class="primary-font">
                                            {{ message.sender.name }}
                                        </strong>
                                    </div>
                                    <p v-bind:class="{ 'pull-right' : check(message.sender.id) , 'pull-left' : !check(message.sender.id)}">
                                        {{ message.text }}
                                    </p>
                                    <div class="row">
                                        <div class="col-md-3" v-for="file in message.files">
                                            <img :src="file.file_details.webPath" alt="" class="img-responsive">
                                            <a :href="file.file_details.webPath" target="_blank" download>Download - {{ file.name }}</a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="panel-footer">
                        <div class="input-group">
                            <input id="btn-input" type="text" v-model="text" class="form-control input-sm" placeholder="Type your message here..." />
                            <span class="input-group-btn">
                            <button class="btn btn-warning btn-sm" type="button" @click.prevent="send()" id="btn-chat">
                                Send
                            </button>
                        </span>
                        </div>
                        <div class="input-group">
                            <input type="file" multiple class="form-control">
                            <span class="input-group-btn">
                            <button class="btn btn-warning btn-sm" type="button" @click.prevent="sendFiles()">
                                Send Files
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="row">
            <div class="col-md-3" v-for="file in conversation.files">
                <img :src="file.file_details.webPath" alt="" class="img-responsive">
                <a :href="file.file_details.webPath" target="_blank" download>Download - {{ file.name }}</a>
            </div>
        </div>
    </div>
</template>
<script>
    $(function () {
        $('input[type=file]').on('change', prepareUpload);
    });

    var files;

    function prepareUpload(event)
    {
        files = event.target.files;
    }

    export default {
        props: ['conversation' , 'currentUser'],
        data() {
            return {
                groupConversationId : this.conversation.group_conversation.id,
                channel : this.conversation.channel_name,
                messages : this.conversation.messages,
                text : '',
            }
        },
        methods: {
            check(id) {
                return id === this.currentUser.id;
            },
            send() {
                axios.post('/group/chat/message/send',{
                    groupConversationId : this.groupConversationId,
                    text: this.text,
                }).then((response) => {
                    this.text = '';
                });
            },
            sendFiles() {
                var data = new FormData();

                $.each(files, function(key, value)
                {
                    data.append('files[]', value);
                });

                data.append('groupConversationId' , this.groupConversationId);

                axios.post('/group/chat/message/send/file', data);
            },
            leaveFromGroup() {
                axios.post('/group/chat/leave/' + this.groupConversationId ).then((response) => {
                    window.location = '/'
                });
            },
            listenForNewMessage: function () {
                Echo.join(this.channel)
                    .here((users) => {
                        console.log(users)
                    })
                    .listen('\\PhpJunior\\LaravelVideoChat\\Events\\NewGroupConversationMessage', (data) => {
                        var self = this;
                        if ( data.files.length > 0 ){
                            $.each( data.files , function( key, value ) {
                                self.conversation.files.push(value);
                            });
                        }
                        this.messages.push(data);
                    });
            },
        },
        mounted() {
            this.listenForNewMessage();
        }
    }
</script>

<style>
    .chat
    {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .chat li
    {
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px dotted #B3A9A9;
    }

    .chat li.left .chat-body
    {
        margin-left: 60px;
    }

    .chat li.right .chat-body
    {
        margin-right: 60px;
    }


    .chat li .chat-body p
    {
        margin: 0;
        color: #777777;
    }

    .panel .slidedown .glyphicon, .chat .glyphicon
    {
        margin-right: 5px;
    }

    .panel-body
    {
        overflow-y: scroll;
        height: 250px;
    }

    ::-webkit-scrollbar-track
    {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        background-color: #F5F5F5;
    }

    ::-webkit-scrollbar
    {
        width: 12px;
        background-color: #F5F5F5;
    }

    ::-webkit-scrollbar-thumb
    {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
        background-color: #555;
    }
</style>