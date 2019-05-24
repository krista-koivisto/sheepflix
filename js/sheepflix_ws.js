var serverURI = "wss://snarkypixel.com/sheepflix/wss/";
var sock;
var wsConnected = false;
var websockID = '<no-id>';
var syncTimer = setTimeout(checkSync, 2500);
var canSync = false;
var wsServerUnavailable = false;
var syncImmunity = false;
var immunityTimer = 0;

var joinTexts = [
                    "says hello!",
                    "peeks in sheepishly...",
                    "sneaks in...",
                    "might have brought popcorn!",
                    "has arrived!",
                    "sits down and stares intently at the screen!",
                    "looks confused but wanders in anyway.",
                    "stumbles in and yelps loudly!",
                    "walks in on a red carpet! Wow!",
                    "sits down next to you.",
                    "enters dramatically!",
                    "plops down and looks thoughtfully at the screen.",
                    "runs in rambling something about missing the show!",
                    "walks in backwards. Why would they even do that?!",
                    "takes a seat and looks pleased with themselves."
                ];
var leaveTexts = [
                    "says baaah-bye!",
                    "runs out screaming!",
                    "has seen enough!",
                    "left with the popcorn! If there ever was any...",
                    "has left!",
                    "is gone!",
                    "didn't apparently enjoy themselves...",
                    "noped out!",
                    "walks out mumbling...",
                    "disappears into the night...",
                    "exits dramatically!",
                    "proclaims ninja allegiance and cartwheels out!",
                    "went poof!",
                    "carefully backs out, staring and pointing at everyone.",
                    "rage quit!"
                ];

function socketSend(data) {
    if (sock.readyState == 1) {
        sock.send(data);
    } else if (wsServerUnavailable == false) {
        showActionMessage("<strong style='color: #FF3333DD'>WARNING:</strong><br>Sheepflix sync server unavailable! Alert the admin if this is unexpected.");
        showActionMessage("... But you can still watch alone if you'd like to stay! Only chat and sync will be unavailable.");
        $("#chatTextArea").prop('disabled', true);
        $("#chatName").prop('disabled', true);
        wsServerUnavailable = true;
    }
}

function receiveMessage(data) {
    addChatMessage(data['name'], data['msg'], data['time'], true);
}

function sendMessage(msg) {
    if (wsConnected) {
        socketSend(JSON.stringify({cmd: "MSG", params: {id: websockID, msg: msg}}));
    } else {
        console.log('Unable to send message. No or incomplete connection to websock interface.');
    }
}

function clearSyncImmunity() {
    syncImmunity = false;
    clearTimeout(immunityTimer);
}

function doPlay(data)
{
    if (data['cmd'] == "PLAY")
    {
        showActionMessage("<strong>" + data['name'] + "</strong> started the video...");
        doSync(data);
        video.play();
        setPlayButton(false);
    }
    else
    {
        showActionMessage("<strong>" + data['name'] + "</strong> paused the video...");
        doSync(data);
        video.pause();
        setPlayButton(true);
    }
}

function doSync(data)
{
    var diff = video.currentTime - data['offset'];

    if (data['isPaused'] != video.paused) {
        if (data['isPaused']) {
            video.pause();
            setPlayButton(true);
        } else {
            video.play();
            setPlayButton(false);
        }
    }

    if (syncImmunity == false)
    {
        if (diff > 3 || diff < -3) {
            showActionMessage("Syncing up with <strong>" + data['name'] + "</strong>...");
            video.currentTime = data['offset'];
        }
    }
}

function checkSync()
{
    clearTimeout(syncTimer);

    // Allow user to sync after they have been on the page long enough to sync with others first.
    canSync = true;

    var data = JSON.stringify({cmd: "SYNC", params: {
        id: websockID,
        offset: video.currentTime,
        isPaused: video.paused
    }});

    socketSend(data);

    if (wsServerUnavailable == false) {
        syncTimer = setTimeout(checkSync, 1000);
    }
}

function parseSheep(data) {
    var cmd = data['cmd'];

    switch (cmd) {
        case "HEY":
            websockID = data['id'];
            socketSend(JSON.stringify({cmd: "ACK", params: {id: websockID}}));
            break;
        case "ACK":
            if (data['id'] == websockID) {
                wsConnected = true;
            }
            break;
        case "MSG":
            receiveMessage(data);
            break;
        case "PLAY":
            doPlay(data);
            break;
        case "PAUSE":
            doPlay(data);
            break;
        case "SYNC":
            doSync(data);
            break;
        case "JOIN":
            showActionMessage("<strong>" + data['name'] + "</strong> " + joinTexts[Math.floor(Math.random() * joinTexts.length)]);
            break;
        case "LEFT":
            showActionMessage("<strong>" + data['name'] + "</strong> " + leaveTexts[Math.floor(Math.random() * leaveTexts.length)]);
            break;
        case "NAME":
            showActionMessage("<strong>" + data['prev_name'] + "</strong> is now known as <strong>" + data['name'] + "</strong>");
            break;
        case "ERR":
            console.log("ERROR: " + data['text']);
        default:
            console.log("Received unsupported command '" + cmd + "' from server. Taking no action!");
            break;
    }
}

function setChatName(name) {
    if (name.trim().length > 0 && name != chatName)
    {
        setCookie('sheep_id', name);
        $("#chatName").val(name);
        chatName = name;

        var data = JSON.stringify({cmd: "NAME", params: {
            id: websockID,
            name: name
        }});

        socketSend(data);
    }
}

function getChatName() {
    var cookieName = getCookie('sheep_id');

    if (cookieName != "") {
        $("#chatName").val(cookieName);
        chatName = cookieName;
    } else {
        setCookie('sheep_id', 'AnonSheep');
    }
}

$(document).ready(function() {
    getChatName();
    sock = new WebSocket(serverURI);

    sock.onopen = function(e) {
        var init = JSON.stringify({cmd: "HEY", params: {session: sessionID, name: chatName}});
        socketSend(init);
    }

    sock.onclose = function(e) {
        console.log('Disconnected');
    };

    sock.onmessage = function(e) {
        parseSheep(jQuery.parseJSON(e.data));
    };

    sock.onerror = function(e) {
        if (e.data != undefined) {
            console.log('Websock error: ' + e.data);
        }
    };
});

function sendPlayPauseEvent() {
    var cmd = "PLAY";

    if (video.paused) {
        cmd = "PAUSE";
    }

    var data = JSON.stringify({cmd: cmd, params: {
        session: sessionID,
        id: websockID,
        offset: video.currentTime
    }});

    if (canSync == true) {
        socketSend(data);
    }
}

/*$("video").on("play", function (e) {

});

$("video").on("pause", function (e) {
    var data = JSON.stringify({cmd: "PAUSE", params: {
        session: sessionID,
        id: websockID,
        offset: video.currentTime
    }});

    if (canSync == true) {
        socketSend(data);
    }
});*/

$(window).on("beforeunload", function() {
    var data = JSON.stringify({cmd: "BYE", params: {
        id: websockID
    }});

    socketSend(data);
})
