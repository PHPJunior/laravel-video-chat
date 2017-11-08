<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-comment"></span> Chat with {{ withUser.name }}

                        <button class="btn btn-warning btn-sm pull-right" @click="startVideoCallToUser(withUser.id)" type="button">
                            <span class="fa fa-video-camera"></span> Video Call
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
            <div class="col-md-6">
                <video-section></video-section>
            </div>

            <div id="incomingVideoCallModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Incoming Call</h4>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="answerCallButton" class="btn btn-success">Answer</button>
                            <button type="button" id="denyCallButton" data-dismiss="modal" class="btn btn-danger">Deny</button>
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
        var localVideo = document.getElementById('localVideo');
        var remoteVideo = document.getElementById('remoteVideo');
        var answerButton = document.getElementById('answerCallButton');

        answerButton.onclick = answerCall;

        $('input[type=file]').on('change', prepareUpload);
    });

    var files;

    var conversationID;
    var luid;
    var ruid;
    var startTime;
    var localStream;
    var pc;
    var offerOptions = {
        offerToReceiveAudio: 1,
        offerToReceiveVideo: 1
    };
    var isCaller = false;
    var peerConnectionDidCreate = false;
    var candidateDidReceived = false;

    export default {
        props: ['conversation' , 'currentUser'],
        data() {
            return {
                conversationId : this.conversation.conversationId,
                channel : this.conversation.channel_name,
                messages : this.conversation.messages,
                withUser : this.conversation.user,
                text : '',
                constraints : {
                    audio: false,
                    video: true
                },
            }
        },
        methods: {
            startVideoCallToUser (id) {

                Cookies.set('remoteUUID', id);

                window.remoteUUID = id;

                luid = Cookies.get('uuid');
                ruid = Cookies.get('remoteUUID');
                isCaller = true;

                start()
            },
            check(id) {
                return id === this.currentUser.id;
            },
            send() {
                axios.post('/chat/message/send',{
                    conversationId : this.conversationId,
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

                data.append('conversationId' , this.conversationId);

                axios.post('/chat/message/send/file', data);
            },
            listenForNewMessage: function () {
                Echo.join(this.channel)
                    .here((users) => {
                        console.log(users)
                    })
                    .listen('\\PhpJunior\\LaravelVideoChat\\Events\\NewConversationMessage', (data) => {
                        var self = this;
                        if ( data.files.length > 0 ){
                            $.each( data.files , function( key, value ) {
                                self.conversation.files.push(value);
                            });
                        }
                        this.messages.push(data);
                    })
                    .listen('\\PhpJunior\\LaravelVideoChat\\Events\\VideoChatStart', (data) => {

                        if(data.to != this.currentUser.id){
                            return;
                        }

                        if(data.type === 'signal'){
                            onSignalMessage(data);
                        }else if(data.type === 'text'){
                            console.log('received text message from ' + data.from + ', content: ' + data.content);
                        }else{
                            console.log('received unknown message type ' + data.type + ' from ' + data.from);
                        }
                    });
            },
        },
        beforeMount () {
            Cookies.set('uuid', this.currentUser.id);
            Cookies.set('conversationID', this.conversationId);
        },
        mounted() {
            this.listenForNewMessage();
        }
    }

    function onSignalMessage(m){
        console.log(m.subtype);
        if(m.subtype === 'offer'){
            console.log('got remote offer from ' + m.from + ', content ' + m.content);
            Cookies.set('remoteUUID', m.from);
            onSignalOffer(m.content);
        }else if(m.subtype === 'answer'){
            onSignalAnswer(m.content);
        }else if(m.subtype === 'candidate'){
            onSignalCandidate(m.content);
        }else if(m.subtype === 'close'){
            onSignalClose();
        }else{
            console.log('unknown signal type ' + m.subtype);
        }
    }
    
    function onSignalClose() {
        trace('Ending call');
        pc.close();
        pc = null;

        closeMedia();
        clearView();
    }

    function closeMedia(){
        localStream.getTracks().forEach(function(track){track.stop();});
    }

    function clearView(){
        localVideo.srcObject = null;
        remoteVideo.srcObject = null;
    }

    function onSignalCandidate(candidate){
        onRemoteIceCandidate(candidate);
    }

    function onRemoteIceCandidate(candidate){
        trace('onRemoteIceCandidate : ' + candidate);
        if(peerConnectionDidCreate){
            addRemoteCandidate(candidate);
        }else{
            //remoteCandidates.push(candidate);
            var candidates = Cookies.getJSON('candidate');
            if(candidateDidReceived){
                candidates.push(candidate);
            }else{
                candidates = [candidate];
                candidateDidReceived = true;
            }
            Cookies.set('candidate', candidates);
        }
    }

    function onSignalAnswer(answer){
        onRemoteAnswer(answer);
    }

    function onRemoteAnswer(answer){
        trace('onRemoteAnswer : ' + answer);
        pc.setRemoteDescription(answer).then(function(){onSetRemoteSuccess(pc)}, onSetSessionDescriptionError);
    }

    function onSignalOffer(offer){
        Cookies.set('offer', offer);
        $('#incomingVideoCallModal').modal('show');
    }

    function answerCall() {
        isCaller = false;
        luid = Cookies.get('uuid');
        ruid = Cookies.get('remoteUUID');
        $('#incomingVideoCallModal').modal('hide');
        start()
    }

    function gotStream(stream) {
        trace('Received local stream');
        localVideo.srcObject = stream;
        localStream = stream;
        call()
    }

    function start() {

        trace('Requesting local stream');

        navigator.mediaDevices.getUserMedia({
            audio: true,
            video: {
                width: {
                    exact: 320
                },
                height: {
                    exact: 240
                }
            }
        })
        .then(gotStream)
        .catch(function(e) {
            alert('getUserMedia() error: ' + e.name);
        });
    }

    function call() {
        conversationID = Cookies.get('conversationID');

        trace('Starting call');
        startTime = window.performance.now();
        var videoTracks = localStream.getVideoTracks();
        var audioTracks = localStream.getAudioTracks();
        if (videoTracks.length > 0) {
            trace('Using video device: ' + videoTracks[0].label);
        }
        if (audioTracks.length > 0) {
            trace('Using audio device: ' + audioTracks[0].label);
        }

        var configuration = { "iceServers": [{ "urls": "stun:stun.ideasip.com" }] };
        pc = new RTCPeerConnection(configuration);

        trace('Created local peer connection object pc');

        pc.onicecandidate = function(e) {
            onIceCandidate(pc, e);
        };

        pc.oniceconnectionstatechange = function(e) {
            onIceStateChange(pc, e);
        };

        pc.onaddstream = gotRemoteStream;

        pc.addStream(localStream);

        trace('Added local stream to pc');

        peerConnectionDidCreate = true;

        if(isCaller) {
            trace(' createOffer start');
            trace('pc createOffer start');

            pc.createOffer(
                offerOptions
            ).then(
                onCreateOfferSuccess,
                onCreateSessionDescriptionError
            );
        }else{
            onAnswer()
        }
    }

    function onAnswer(){
        var remoteOffer = Cookies.getJSON('offer');

        pc.setRemoteDescription(remoteOffer).then(function(){onSetRemoteSuccess(pc)}, onSetSessionDescriptionError);

        pc.createAnswer().then(
            onCreateAnswerSuccess,
            onCreateSessionDescriptionError
        );
    }

    function onCreateAnswerSuccess(desc) {
        trace('Answer from pc:\n' + desc.sdp);
        trace('pc setLocalDescription start');
        pc.setLocalDescription(desc).then(
            function() {
                onSetLocalSuccess(pc);
            },
            onSetSessionDescriptionError
        );
        conversationID = Cookies.get('conversationID');
        var message = {from: luid, to:ruid, type: 'signal', subtype: 'answer', content: desc, time:new Date()};
        axios.post('/trigger/' + conversationID , message );
    }

    function onSetRemoteSuccess(pc) {
        trace(pc + ' setRemoteDescription complete');
        applyRemoteCandidates();
    }

    function applyRemoteCandidates(){
        var candidates = Cookies.getJSON('candidate');
        for(var candidate in candidates){
            addRemoteCandidate(candidates[candidate]);
        }
        Cookies.remove('candidate');
    }

    function addRemoteCandidate(candidate){
        pc.addIceCandidate(candidate).then(
            function() {
                onAddIceCandidateSuccess(pc);
            },
            function(err) {
                onAddIceCandidateError(pc, err);
            });
    }

    function onIceCandidate(pc, event) {
        if (event.candidate){
            trace(pc + ' ICE candidate: \n' + (event.candidate ? event.candidate.candidate : '(null)'));
            conversationID = Cookies.get('conversationID');
            var message = {from: luid, to:ruid, type: 'signal', subtype: 'candidate', content: event.candidate, time:new Date()};
            axios.post('/trigger/' + conversationID , message );
        }
    }

    function onAddIceCandidateSuccess(pc) {
        trace(pc + ' addIceCandidate success');
    }

    function onAddIceCandidateError(pc, error) {
        trace(pc + ' failed to add ICE Candidate: ' + error.toString());
    }

    function onIceStateChange(pc, event) {
        if (pc) {
            trace(pc + ' ICE state: ' + pc.iceConnectionState);
            console.log('ICE state change event: ', event);
        }
    }

    function onCreateSessionDescriptionError(error) {
        trace('Failed to create session description: ' + error.toString());
    }

    function onCreateOfferSuccess(desc) {
        trace('Offer from pc\n' + desc.sdp);
        trace('pc setLocalDescription start');
        pc.setLocalDescription(desc).then(
            function() {
                onSetLocalSuccess(pc);
            },
            onSetSessionDescriptionError
        );

        conversationID = Cookies.get('conversationID');
        var message = {from: luid, to:ruid, type: 'signal', subtype: 'offer', content: desc, time:new Date()};
        axios.post('/trigger/' + conversationID , message );
    }

    function onSetLocalSuccess(pc) {
        trace( pc + ' setLocalDescription complete');
    }

    function onSetSessionDescriptionError(error) {
        trace('Failed to set session description: ' + error.toString());
    }

    function gotRemoteStream(e) {
        if (remoteVideo.srcObject !== e.stream) {
            remoteVideo.srcObject = e.stream;
            trace('pc received remote stream');
        }
    }

    function trace(arg) {
        var now = (window.performance.now() / 1000).toFixed(3);
        console.log(now + ': ', arg);
    }

    function prepareUpload(event)
    {
        files = event.target.files;
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