<?php

namespace Src;

class Server
{

    private string $Method;

    public function __construct()
    {
        $this->Method = $_SERVER['REQUEST_METHOD'];
    }

    public function returnResponse(int $status = 404, array $Json = ['error' => 'Not Found']): void
    {
        http_response_code($status);
        Header('Content-type: application/json, charset=utf-8');

        echo json_encode($Json);
    }

    public function getMethod(): string
    {
        return $this->Method;
    }

}