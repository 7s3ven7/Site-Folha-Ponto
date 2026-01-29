<?php

use Src\DataManipulator;
use Src\Server;

require_once 'Src/Server.php';
require_once 'Src/DataManipulator.php';

$server = new Server();
$dataManipulator = new DataManipulator();

$body = file_get_contents('php://input');

switch ($server->getMethod()) {
    case 'GET':

        $object = $dataManipulator->createObject($body);

        if (is_array($object)) {
            $server->response(400, $object);
            break;
        }

        $server->response(201, $object);

        break;
    default:
        $server->response(404, ["message" => "Method not allowed"]);
        break;
}
