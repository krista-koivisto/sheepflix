<?php
include_once("sheepsocks.php");
include_once("sheepwebsocks.php");
include_once("sheepmoji.php");
include_once("Sheep.php");

// This will hold all connected Sheep
$sheep = array();
// This secret key is used to generate unique sheep IDs, change it!
$secret_key = "Sheepflix is totally the sheep!!!";
// The longer the ID, the more secure but at a cost of higher data use (max: 32)
$id_length = 16;

$sheepProtocol = array("HEY", "ACK", "BYE", "MSG", "PLAY", "PAUSE", "SYNC", "NAME");

function forceSheepDisconnect($client)
{
    global $sheep;

    foreach ($sheep as $sheepo)
    {
        if ($sheepo->client == $client)
        {
            sheepClientDisconnect($sheepo->id, false);
            break;
        }
    }
}

function sheepClientConnect($client, $params)
{
    global $sheep, $secret_key, $id_length;

    $id = substr(hash_hmac("md5", time(0), $secret_key), 0, $id_length);
    $sheepo = new Sheep($client, $id, $params['session'], $params['name']);
    $sheep[$id] = $sheepo;

    $cmd = array('cmd' => 'HEY', 'id' => $id);

    clientWrite($client, json_encode($cmd));
}

function sheepClientAck($id)
{
    global $sheep;

    if (verifySheep($id, "ACK"))
    {
        $sheepo = $sheep[$id];

        $sheepo->setConnected();

        clientWrite($sheepo->client, json_encode(array('cmd' => 'ACK', 'id' => $id)));
        sendSheep(json_encode(array('cmd' => 'JOIN', 'name' => $sheepo->name)), $sheepo->session);
    }
}

function sheepClientDisconnect($id, $socketClose = true)
{
    global $sheep;

    if (verifySheep($id, "BYE"))
    {
        $sheepo = $sheep[$id];

        if ($socketClose)
        {
            disconnectClient($sheepo->client, false);
        }

        $name = $sheepo->name;
        $session = $sheepo->session;
        unset($sheep[$id]);

        sendSheep(json_encode(array('cmd' => 'LEFT', 'name' => $name)), $session);
    }
}

function verifySheep($id, $type = "Data")
{
    global $sheep;

    if (isset($sheep[$id]))
    {
        return true;
    }

    echo "$type from unknown sheep: '$id'!\n";
    return false;
}

function sheepPlay($params, $play = true)
{
    global $sheep;

    $cmd = 'PLAY';

    if ($play === false)
    {
        $cmd = 'PAUSE';
    }

    $sheepo = $sheep[$params['id']];

    $data = json_encode(array(  'cmd' => $cmd,
                                'name' => $sheepo->name,
                                'offset' => $params['offset']
                            ));

    sendSheep($data, $sheepo->session, $sheepo);
}

function sheepSync($params)
{
    global $sheep;

    $sheepo = $sheep[$params['id']];

    $data = json_encode(array(  'cmd' => "SYNC",
                                'name' => $sheepo->name,
                                'offset' => $params['offset'],
                                'isPaused' => $params['isPaused']
                            ));

    sendSheep($data, $sheepo->session, $sheepo);
}

function parseSheep($client, $msg)
{
    global $sheep, $sheepProtocol, $id_length;

    $data = json_decode($msg, true);
    $cmd = $data['cmd'];
    $params = $data['params'];

    if (in_array($cmd, $sheepProtocol))
    {
        switch($cmd)
        {
            case "HEY":
                sheepClientConnect($client, $params);
                break;
            case "ACK":
                sheepClientAck($params['id']);
                break;
            case "BYE":
                sheepClientDisconnect($params['id'], true);
                break;
            case "MSG":
                $id = $params['id'];

                if (verifySheep($id, "MSG"))
                {
                    $sheepo = $sheep[$id]->name;

                    if (strlen($params['msg']) >= 1)
                    {
                        $data = json_encode(array(  'cmd' => 'MSG',
                                                    'name' => $sheepo,
                                                    'msg' => parseEmoji(htmlentities($params['msg'])),
                                                    'time' => time()));
                        sendSheep($data, $sheep[$id]->session);
                    }
                }
                break;
            case "PLAY":
                if (verifySheep($params['id'], "PLAY"))
                {
                    sheepPlay($params, true);
                }
                break;
            case "PAUSE":
                if (verifySheep($params['id'], "PAUSE"))
                {
                    sheepPlay($params, false);
                }
                break;
            case "SYNC":
                if (verifySheep($params['id'], "SYNC"))
                {
                    sheepSync($params);
                }
                break;
            case "NAME":
                $id = $params['id'];

                if (verifySheep($id, "NAME"))
                {
                    $prev_name = $sheep[$id]->name;
                    $name = $params['name'];

                    if (strlen($name))
                    {
                        if (strlen($name) > 12)
                        {
                            $name = substring($name, 0, 12);
                        }

                        $sheep[$id]->name = $params['name'];

                        $data = json_encode(array(  'cmd' => 'NAME',
                                                    'name' => $name,
                                                    'prev_name' => $prev_name
                                                ));

                        sendSheep($data, $sheep[$id]->session);
                    }
                }
                break;
            default:
                break;
        }
    }
    else
    {
        echo "Received non-sheepy data from client!\n";

        clientWrite($client, json_encode(array(
                                        "cmd" => "ERR",
                                        "text" => "I don't know what you're talking about! Would you speak proper Sheep, please! :("
                            )));
    }
}

function sendSheep($msg, $session = 0, $ignoreSheep = 0)
{
    global $sheep;

    foreach ($sheep as $sheepo)
    {
        if ($session === 0 || $sheepo->session === $session)
        {
            if ($sheepo !== $ignoreSheep)
            {
                clientWrite($sheepo->client, $msg);
            }
        }
    }
}
