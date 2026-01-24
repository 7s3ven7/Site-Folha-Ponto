<?php

use Project\Src\User;

require_once 'Src/User.php';

$user = new User();

$body = json_decode(file_get_contents('php://input'), true);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($body['id'])) {
            echo json_encode($user->getUser($body['id']));
        } else {
            echo json_encode($user->getUsers());
        }
        break;
    case 'POST':
        $name = $body['name'] ?? null;
        $password = $body['password'] ?? null;
        echo $user->saveUser($name, $password);
        break;
    case 'PUT':
        (int)$id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $password = $body['password'] ?? null;
        echo $user->modifyUser($id, $name, $password);
        break;
    case 'PATCH':
        (int)$id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $password = $body['password'] ?? null;
        echo $user->partialModifyUser($id, $name, $password);
        break;
    case 'DELETE':
        (int)$id = $body['id'] ?? null;
        echo $user->deleteUser($id);
        break;
}