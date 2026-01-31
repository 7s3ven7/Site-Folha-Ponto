<?php

namespace Src;

class Server
{

    private string $method;

    public function __construct()
    {

        $this->method = $_SERVER['REQUEST_METHOD'];

    }

    public function response(int $code, array $response): void
    {

        http_response_code($code);

        Header("Content-type: application/json");

        print_r(json_encode($response));

    }

    public function getMethod(): string
    {

        return $this->method;

    }

}