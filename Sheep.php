<?php
class Sheep
{
    private $connected = false;

    public $id = 0;
    public $client = null;
    public $name = "AnonSheep";
    public $session = 0;

    public function __construct($client, $id, $session, $name = "AnonSheep")
    {
        if (strlen($name) == 0)
        {
            $name = "AnonSheep";
        }
        else if (strlen($name) > 12)
        {
            $name = substr($name, 0, 12);
        }

        $this->client = $client;
        $this->id = $id;
        $this->name = $name;
        $this->session = $session;
    }

    public function setConnected()
    {
        $this->connected = true;
    }

    public function __destruct()
    {
        $this->connected = false;
    }
}
