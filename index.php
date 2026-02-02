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
        $fileCsv = $csv->getContentCSV();
        if (isset($fileCsv['error'])) {
            $server->response(400, $fileCsv);
            break;
        }
        $server->response(200, $fileCsv);
        break;
    case 'POST':
        $fileCsv = $csv->writeCSV($body['data'], $body['read']);
        if (isset($fileCsv['error'])) {
            $server->response(406, $fileCsv);
            break;
        }
        $server->response(201, $fileCsv);
        break;
    case 'DELETE':
        $response = $csv->deleteCSV();
        if (isset($response['error'])) {
            $server->response(406, $response);
            break;
        }
        $server->response(200, $response);
        break;
    default:
        $server->response(404, ['error' => 'not allowed this method']);
}