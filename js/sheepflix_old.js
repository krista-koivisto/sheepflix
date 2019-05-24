var chatBox = $(".chatText");
var video = document.getElementById("videoPlayer");
var userVolume = false;
var isFullScreen = false;
var lastMsg = 0;
var chatName = "AnonSheep";

function setCookie(name, value)
{
    document.cookie = name + "=" + value + ";path=/";
}

function getCookie(name) {
    var cookie = decodeURIComponent(document.cookie);
    var start_index = cookie.indexOf(name + "=") + name.length + 1;
    var val_length = cookie.substring(start_index).indexOf(';');

    if (val_length == -1) {
        return cookie.substring(start_index);
    } else {
        return cookie.substring(start_index, val_length + start_index);
    }
}

function showActionMessage(msg) {
    chatBox.append("<div class='chatMsg notification'>" + msg + "</div>");
    chatBox.animate({ scrollTop: chatBox.prop("scrollHeight")}, 200);
}

function addChatMessage(sender, msg, timestamp, doScroll = false) {
    var msgTime = new Date(timestamp * 1000);
    var hours = msgTime.getHours().toString();
    var minutes = msgTime.getMinutes().toString();

    if (hours.length == 1) hours = "0" + hours;
    if (minutes.length == 1) minutes = "0" + minutes;

    var messageBox = "<div class='chatMsg'>";
    messageBox += "<div class='msgSender'>" + sender + "<span class='msgTime'>" + hours + ":" + minutes + "</span></div>";
    messageBox += "<div class='msgContent'>" + twemoji.parse(msg) + "</div>";
    messageBox += "</div>";

    chatBox.append(messageBox);

    if (doScroll == true)
    {
        chatBox.animate({ scrollTop: chatBox.prop("scrollHeight")}, 200);
    }
}

/*function updateChat(sync) {
    if (sync.chat.length > 0) {
        for (var i = 0; i < sync.chat.length; i++) {
            if (sync.chat[i].msg != undefined) {
                addChatMessage(sync.chat[i].s, twemoji.parse(sync.chat[i].msg), sync.chat[i].t);
                lastMsg = sync.chat[i].t;
            }
        }

        chatBox.animate({ scrollTop: chatBox.prop("scrollHeight")}, 200);
    }
}*/

function sendChat(msg) {
    sendMessage(msg);
    /*var chatName = $("#chatName").val();
    setCookie('sheep_id', chatName);

    var jqxhr = $.ajax({
        method: "POST",
        url: "action.php",
        data: {
            lastChat: lastMsg,
            movie: movie,
            c_id: chatName,
            msg: msg,
            action: 'chat_msg'
        }
    })
    .done(function(resp) {
        var sync = jQuery.parseJSON(resp);
        updateChat(sync);
    });*/
}

function sync(playChanged) {
    /*var videoAction = 'sync';
    var chatName = $("#chatName").val();
    var isHost = <?php echo ($_GET['host'] == 1 ? "1" : "0") ?>;

    if (playChanged)
    {
        videoAction = ( video.paused ? 'pause' : 'play' );
    }

    var jqxhr = $.ajax({
        method: "POST",
        url: "action.php",
        data: {
            c_id: chatName,
            movie: movie,
            offset: video.currentTime,
            action: videoAction,
            host: isHost,
            syncOffset: !firstSync,
            lastChat: lastMsg
        }
    })
    .done(function(resp) {
        var status = jQuery.parseJSON(resp);

        if (playChanged !== true)
        {
            var chatBox = $(".chatText");

            if (status['play'] == 1)
            {
                if (video.paused)
                {
                    showActionMessage("<strong>" + status['action_user'] + "</strong> started the video...");
                    video.currentTime = status['offset'];
                    video.play();
                }
            }
            else
            {
                if (!video.paused)
                {
                    showActionMessage("<strong>" + status['action_user'] + "</strong> paused the video...");
                    video.currentTime = status['offset'];
                    video.pause();
                }
            }

            var diff = video.currentTime - status['offset'];

            if (diff > 3 || diff < -3)
            {
                video.currentTime = status['offset'];
            }
        }

        updateChat(status);
    });

    if (firstSync)
    {
        firstSync = false;
    }

    clearTimeout(syncTimer);
    syncTimer = setTimeout(sync, 1000);*/
}

function autoUnmute() {
    if (userVolume == false) {
        video.muted = false;
    }
}

function fullscreenSwitch() {
    var i = document.getElementById("videoArea");

    if (isFullScreen) {
        document.webkitCancelFullScreen();
    } else {
        i.webkitRequestFullscreen();
    }

    isFullScreen = !isFullScreen;
}

$("textarea").on("keydown", function (e) {
    var txt = $(this);

    if (e.which == 13)
    {
        var msg = txt.val();
        txt.val('');
        sendChat(msg);

        return false;
    }
});

$("video").on("webkitfullscreenchange", function (e) {
    document.webkitCancelFullScreen();

    fullscreenSwitch();
});

$("video").on("volumechange", function (e) {
    userVolume = true;
});

$("body").on("click", function (e) {
    autoUnmute();
});

$("video").on("click", function (e) {
    autoUnmute();
});

/*$("video").on("play", function (e) {
    sync(true);
});

$("video").on("pause", function (e) {
    sync(true);
});*/

function resizeChat() {
    videoHeight = $("video").outerHeight();
    $(".chatText").height(videoHeight - 160);
    $(".chatArea").height(videoHeight - 140);
}

$(window).on('load', function() {
    resizeChat();
});

$(window).on('resize', function() {
    resizeChat();
});
