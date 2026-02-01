<?php

require_once('Src/Server.php');
require_once('Src/Csv.php');

use Src\Server;
use Src\Csv;

$server = new Server();
$body = json_decode(file_get_contents('php://input'), true);

$csv = new CSV($body['filename'], $body['path']);
switch ($server->getMethod()) {
    case 'GET':
        $fileCsv = $csv->writeCSV([], $body['read']);
        $server->response(201, $fileCsv);
        break;
    case 'POST':
        break;
    default:
        $server->response(404, ['error' => 'not allowed this method']);
}