<?php
error_reporting(~E_NOTICE);
set_time_limit(0);

$address = "0.0.0.0"; // Bind address
$port = 5000; // Bind port
$max_clients = 10; // Maximum number of clients that can join

$clients = array();

function clientConnected($client, $address, $port)
{
    $bytes =  @socket_recv($client, $headers, 2048, 0);
    websockHandshake($client, $headers);
}

function clientWrite($client, $msg)
{
    socket_write($client, websockEncode($msg));
}

function returnError($error)
{
    die($error['json']);
}

function errorReport($code, $msg, $attempt = "")
{
    $error['error'] = true;
    $error['report'] = "Encountered an error while attempting to " . $attempt . "!";
    $error['code'] = $code;
    $error['msg'] = $msg;
    $error['json'] = json_encode($error);

    return $error;
}

function validate($retval)
{
    return (!isset($retval['error']) || $retval['error'] !== true);
}

function createSocket()
{
    if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
    {
        socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
        $err_code = socket_last_error();
        $err_msg = socket_strerror($errorcode);

        return errorReport($err_code, $err_msg, "create a socket");
    }

    return $sock;
}

function bindSocket($sock, $address, $port)
{
    // Bind the source address
    if(!socket_bind($sock, $address, $port))
    {
        $err_code = socket_last_error();
        $err_msg = socket_strerror($errorcode);

        return errorReport($err_code, $err_msg, "bind the socket");
    }
}

function socketListen($sock, $max_clients)
{
    if(!socket_listen ($sock , $max_clients))
    {
        $err_code = socket_last_error();
        $err_msg = socket_strerror($errorcode);

        return errorReport($err_code, $err_msg, "listen for connections");
    }
}

function getClients($read, $client_socks, $max_clients)
{
    for ($i = 0; $i < $max_clients; $i++)
    {
        if($client_socks[$i] != null)
        {
            $read[$i+1] = $client_socks[$i];
        }
    }

    return $read;
}

function selectSocket($read)
{
    if(socket_select($read, $write, $except, null) === false)
    {
        $err_code = socket_last_error();
        $err_msg = socket_strerror($errorcode);

        return errorReport($err_code, $err_msg, "select client sockets");
    }

    return $read;
}

function processConnections($sock, $read, $client_socks, $max_clients = 10)
{
    // If read contains the master socket, then a new connection has come in
    if (in_array($sock, $read))
    {
        for ($i = 0; $i < $max_clients; $i++)
        {
            if ($client_socks[$i] == null)
            {
                $client_socks[$i] = socket_accept($sock);

                if(socket_getpeername($client_socks[$i], $address, $port))
                {
                    clientConnected($client_socks[$i], $address, $port);
                }
                break;
            }
        }
    }

    return $client_socks;
}

function processClient($client, $id)
{
    $input = socket_read($client , 1024);

    if ($input == null)
    {
        disconnectClient($client);
        return false;
    }

    $input = websockUnmask($input);
    parseSheep($client, trim($input));
}

function disconnectClient($client, $removeSheep = true)
{
    global $clients;

    if ($removeSheep)
    {
        forceSheepDisconnect($client);
    }

    if (($id = array_search($client, $clients)) !== false)
    {
        socket_close($clients[$id]);
        unset($clients[$id]);
    }
    else
    {
        die("FATAL ERROR: Unable to find socket to close on client disconnect! Losing track of sockets!");
    }
}

function serverListen($sock, $max_clients = 10)
{
    global $clients;

    // Listen for incoming connections and process existing connections
    while (true)
    {
        $read = array();

        // The first socket is the master socket
        $read[0] = $sock;

        $read = getClients($read, $clients, $max_clients);

        // NOTE: select is a blocking call
        $read = selectSocket($read);

        // Check for clients connecting to the server
        $clients = processConnections($sock, $read, $clients, $max_clients);

        // Check each client for incoming data
        for ($i = 0; $i < $max_clients; $i++)
        {
            if (in_array($clients[$i], $read))
            {
                processClient($clients[$i], $i);
            }
        }
    }
}

function startServer()
{
    global $address, $port, $max_clients;

    $ret = createSocket();

    if (validate($ret))
    {
        $sock = $ret;

        $ret = bindSocket($sock, $address, $port);
        if (validate($ret))
        {
            $ret = socketListen($sock, $max_clients);
            if (validate($ret))
            {
                serverListen($sock, $max_clients);
            }
        }
    }

    returnError($ret);
}
?>
