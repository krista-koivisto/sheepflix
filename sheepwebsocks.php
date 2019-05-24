<?php
function websockHandshake($client, $headers)
{
    if (preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match))
    {
        $version = $match[1];
    }
    else
    {
        echo "Sheep without WebSocket support attempted to connect!\n";
        return false;
    }

    if ($version == 13)
    {
        // Extract the secure websock key
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match))
        {
            $secureKey = $match[1];
        }

        $accept = $secureKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $accept = base64_encode(sha1($accept, true));

        $upgrade =  "HTTP/1.1 101 Switching Protocols\r\n".
                    "Upgrade: websocket\r\n".
                    "Connection: Upgrade\r\n".
                    "Sec-WebSocket-Accept: $accept".
                    "\r\n\r\n";

        socket_write($client, $upgrade);
        return true;
    }
    else
    {
        print("Sheep reported incorrect WebSocket version ({$version}), I only know how to speak 13! :(");
        return false;
    }
}

function websockUnmask($payload)
{
    $length = ord($payload[1]) & 127;

    if ($length == 126)
    {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    }

    elseif($length == 127)
    {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    }

    else
    {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }

    $text = '';

    for ($i = 0; $i < strlen($data); ++$i)
    {
        $text .= $data[$i] ^ $masks[$i % 4];
    }

    return $text;
}

function websockEncode($text)
{
    // 0x1 text frame (FIN + opcode)
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125)
    {
        $header = pack('CC', $b1, $length);
    }
    else if($length > 125 && $length < 65536)
    {
        $header = pack('CCn', $b1, 126, $length);
    }
    else if($length >= 65536)
    {
        $header = pack('CCN', $b1, 127, $length);
    }

    return $header . $text;
}
