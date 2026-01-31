<?php

require_once('Src/Server.php');

use Src\Server;

$server = new Server();

switch ($this->method) {
    case 'GET':
        break;
    case 'POST':
        break;
    default:
        $server->response(404, ['error', 'not allowed this method']);
}