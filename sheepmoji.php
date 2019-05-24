<?php
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
