var chatBox = $(".chatText");
var video = document.getElementById("videoPlayer");
var userVolume = false;
var isFullScreen = false;
var lastMsg = 0;
var chatName = "AnonSheep";

var controlsFaded = false;
var fadeTimer = setTimeout(fadeVideoControls, 1000);
var seekbarControl = $("#seekbarControl");
var timeDisplay = $("#timeDisplay");
var seekUpdate = setTimeout(updateSeekbar, 1000);

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

function decorateMessage(msg)
{
    msg = twemoji.parse(msg);
    msg = msg.replace(/\*(.+?)\*/g, "<i>$1</i>");
    return msg;
}

function addChatMessage(sender, msg, timestamp, doScroll = false) {
    var msgTime = new Date(timestamp * 1000);
    var hours = msgTime.getHours().toString();
    var minutes = msgTime.getMinutes().toString();

    if (hours.length == 1) hours = "0" + hours;
    if (minutes.length == 1) minutes = "0" + minutes;

    var messageBox = "<div class='chatMsg'>";
    messageBox += "<div class='msgSender'>" + sender + "<span class='msgTime'>" + hours + ":" + minutes + "</span></div>";
    messageBox += "<div class='msgContent'>" + decorateMessage(msg) + "</div>";
    messageBox += "</div>";

    chatBox.append(messageBox);

    if (doScroll == true)
    {
        chatBox.animate({ scrollTop: chatBox.prop("scrollHeight")}, 200);
    }
}

function autoUnmute() {
    if (userVolume == false) {
        video.muted = false;
    }
}

function sendChat() {
    var txt = $("textarea");

    var msg = txt.val();
    txt.val('');
    sendMessage(msg);
}

function updateName() {
    setChatName($("#chatName").val());
}

$("textarea").on("keydown", function (e) {
    if (e.which == 13) {
        sendChat();
        return false;
    }
});

$("#chatName").on("keydown", function (e) {
    if (e.which == 13) {
        updateName();
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
    if (userVolume == false) {
        autoUnmute();
    } else {
        if (video.paused == true) {
            playVideo();
        } else {
            pauseVideo();
        }

        sendPlayPauseEvent();
    }
}).on("dblclick", function (e) {
    fullscreenSwitch();
});

function resizeChat() {
    videoHeight = $("video").outerHeight();
    $(".chatText").height(videoHeight - 160);
    $(".chatArea").height(videoHeight - 140);
    $(".chatText").css('maxWidth', videoHeight - 140 + 'px');
    $(".chatArea").css('maxWidth', videoHeight - 140 + 'px');
}

function resizeControls() {
    videoWidth = $("video").outerWidth();
    $(".videoControl.seekbar").css('width', videoWidth - 324 + 'px');
}

function updateSize() {
    resizeChat();
    resizeControls();
}

$(window).on('load', function() {
    updateSize();
});

$(window).on('resize', function() {
    updateSize();
});

// Video Controls
function fullscreenSwitch() {
    var videoArea = document.getElementById("videoArea");

    if (isFullScreen) {
        document.webkitCancelFullScreen();
        setTimeout(updateSize, 250);
    } else {
        videoArea.webkitRequestFullscreen();
    }

    isFullScreen = !isFullScreen;
}

function setPlayButton(paused) {
    if (paused) {
        $("#playPauseImage").removeClass('fa-pause');
        $("#playPauseImage").addClass('fa-play');
    } else {
        $("#playPauseImage").addClass('fa-pause');
        $("#playPauseImage").removeClass('fa-play');
    }
}

function playVideo() {
    setPlayButton(false);
    video.play();
}

function pauseVideo() {
    setPlayButton(true);
    video.pause();
}

function updateSeekbar() {
    clearTimeout(seekUpdate);
    seekUpdate = setTimeout(updateSeekbar, 1000);

    if (syncImmunity == false)
    {
        var value = (100 / video.duration) * video.currentTime;
        seekbarControl.val(value);
    }

    updateTimeDisplay();
}

function updateTimeDisplay() {
    var time = video.currentTime;

    var hours = Math.floor(time / 3600).toString();
    time -= hours * 3600;

    var minutes = Math.floor(time / 60).toString();
    time -= minutes * 60;

    var seconds = Math.floor(time).toString();

    if (hours.length == 1) hours = "0" + hours;
    if (minutes.length == 1) minutes = "0" + minutes;
    if (seconds.length == 1) seconds = "0" + seconds;

    timeDisplay.text(hours + ":" + minutes + ":" + seconds);
}

function fadeVideoControls() {
    $("#videoControls").fadeTo(1000, 0.0);
    controlsFaded = true;
    clearTimeout(fadeTimer);
}

$("#playPause").on("click", function() {
    if (video.paused == true) {
        playVideo();
    } else {
        pauseVideo();
    }

    sendPlayPauseEvent();
});

$("#muteControl").on("click", function() {
    if (video.muted == true) {
        video.muted = false;
    } else {
        video.muted = true;
    }
});

$("#fullscreenControl").on("click", function() {
    fullscreenSwitch();
});

seekbarControl.on("change", function() {
    clearTimeout(seekUpdate);
    seekUpdate = setTimeout(updateSeekbar, 1000);

    if (canSync)
    {
        var time = video.duration * ($(this).val() / 100);
        video.currentTime = time;

        syncImmunity = true;
        clearTimeout(immunityTimer);
        immunityTimer = setTimeout(clearSyncImmunity, 1000);
    }
});

function displayControls() {
    clearTimeout(fadeTimer);
    fadeTimer = setTimeout(fadeVideoControls, 3000);

    if (controlsFaded) {
        $("#videoControls").stop().fadeTo("fast", 0.75);
        controlsFaded = false;
    }
}

$(document).on("mousemove", function() {
    displayControls();
});

$("video").on("mousemove", function() {
    displayControls();
});

$("#volumeControl").on("change", function() {
    video.volume = $(this).val();
});
