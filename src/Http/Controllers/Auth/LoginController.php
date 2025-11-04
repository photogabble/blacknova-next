<?php

namespace BlackNova\Http\Controllers\Auth;

use BlackNova\Http\Controllers\Controller;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LoginController extends Controller{
    public function showLoginForm(
        ServerRequestInterface $request,
        array $args
    ): ResponseInterface {
        return $this->view('test.tpl');
    }
}