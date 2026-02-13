<?php

require_once('vendor/autoload.php');

use App\Entity\Excel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello World!");
    return $response;
});

$app->group('/Planilha', function (RouteCollectorProxy $group) {
    $group->get('/{fileName}/{path}', function (Request $request, Response $response, array $args) {
        $fileName = $args['fileName'];
        $path = json_decode($args['path']);

        $excel = new Excel();
        $excel->setMetaData($fileName, $path);
        $excel->getFile();

        return $response;
    });

    $group->post('/{fileName}/{path}', function (Request $request, Response $response, array $args) {
        $body = json_decode($request->getBody()->getContents());
        $fileName = $args['fileName'];
        $path = json_decode($args['path']);
        $data = $body->data;

        $excel = new Excel();

        $excel->setMetaData($fileName, $path);
        $excel->writeData($data);
        $excel->saveFile();

        //$response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //$response->withHeader('Content-Disposition', 'attachment;filename="' . $fileName . '.xlsx"');
        //$response->withHeader('Cache-Control', 'max-age=0');

        return $response;
    });
});

$app->run();