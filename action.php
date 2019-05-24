<?php

$isFirstRun = false;

$movie = $_POST['movie'];
$offset = $_POST['offset'];
$action = $_POST['action'];
$isHost = $_POST['host'];
$syncOffset = $_POST['syncOffset'];
$lastChat = $_POST['lastChat'];
$actionUser = htmlentities($_POST['c_id']);
$newMsgs = "";

$status = array();
$status['movie'] = $_POST['movie'];
$status['offset'] = 0;
$status['play'] = 1;
$status['syncOffset'] = $syncOffset;

$filename = "plays/" . $movie["id"] . ".play";
$status['file'] = $filename;
$status['chat'][] = array();
$statusFile = fopen($filename, "r");

// Emoji definitions go here
$emoji = array(
    ":hugging:" => "\u{1F917}",
    ":hug:" => "\u{1F917}",
    ":heart:" => "\u{2764}",
    "&lt;3" => "\u{2764}",
    ":kiss:" => "\u{1F61A}",
    ":flushed:" => "\u{1F633}",
    ":*" => "\u{1F61A}",
    ":P" => "\u{1F61B}",
    ":)" => "\u{1F642}",
    ":(" => "\u{2639}\u{FE0F}",
    "-_-" => "\u{1F611}",
    ":|" => "\u{1F610}",
    ":thinking:" => "\u{1F914}",
    ":hmm:" => "\u{1F914}",
    ":grimacing:" => "\u{1F62C}",
    ":smirk:" => "\u{1F60F}",
    "&gt;;)" => "\u{1F60F}",
    ":scream:" => "\u{1F631}",
    ":'(" => "\u{1F622}",
    ":cry:" => "\u{1F622}",
    ":crying:" => "\u{1F622}",
    ":sad:" => "\u{1F622}",
    ":zzz:" => "\u{1F62A}",
    ":ehh:" => "\u{1F928}",
    ":sleepy:" => "\u{1F62A}",
    "*monkeynosee*" => "\u{1F648}",
    ":monkeynosee:" => "\u{1F648}",
    ":seenoevil:" => "\u{1F648}",
    "*seenoevil*" => "\u{1F648}",
    "*monkey no see*" => "\u{1F648}",
    ":monkey_no_see:" => "\u{1F648}",
    ":see_no_evil:" => "\u{1F648}",
    ":see-no-evil:" => "\u{1F648}",
    ":burger:" => "\u{1F354}",
    ":popcorn:" => "\u{1F37F}",
    ":saaad:" => "\u{1F626}",
    ":anguished:" => "\u{1F627}",
    ":wink:" => "\u{1F609}",
    ";)" => "\u{1F609}",
    ":facepalm:" => "\u{1F926}",
);

function parseEmoji($msg)
{
    global $emoji;

    foreach ($emoji as $key => $val)
    {
        $msg = preg_replace('/' . preg_quote($key) . '/', $val, $msg);
    }

    return $msg;
}

function getNewMessages($lastChat, $chatLog)
{
    $chats = $chatLog;
    $newMsgs = array();

    foreach($chats as $msg)
    {
        if ($msg['t'] > $lastChat)
        {
            $newMsgs[] = parseEmoji($msg);
        }
    }

    return $newMsgs;
}

// If no file exists yet, it means it's our first run so don't sync
if (!isset($statusFile) || !$statusFile)
{
    $isFirstRun = true;
}
else
{
    $status = array_replace($status, json_decode(fread($statusFile, filesize($filename)), true));
    fclose($statusFile);
}

if (!$isFirstRun)
{
    switch($action)
    {
    case 'chat_msg':
        $chat = array();
        $chat['s'] = $actionUser;
        $chat['msg'] = htmlentities($_POST['msg']);
        $chat['t'] = time();
        $status['chat'][] = $chat;
        break;
    case 'play':
        if (isset($actionUser) && strlen($actionUser))
        {
            $status['play'] = '1';
            $status['offset'] = $offset;
            $status['action_user'] = $actionUser;
        }
        break;
    case 'pause':
        if (isset($actionUser) && strlen($actionUser))
        {
            $status['play'] = '0';
            $status['offset'] = $offset;
            $status['action_user'] = $actionUser;
        }
        break;
    case 'sync':
        if ($isHost/* && $syncOffset == "true"*/)
        {
            $status['offset'] = $offset;
        }

        $newMsgs = getNewMessages($lastChat, $status['chat']);
        break;
    default:
        break;
    }
}

$statusJSON = json_encode($status);

$statusFile = fopen($filename, "w") or die("Unable to open status file!");
fwrite($statusFile, $statusJSON);
fclose($statusFile);

$status['chat'] = $newMsgs;
$statusJSON = json_encode($status);
die($statusJSON);

?>
