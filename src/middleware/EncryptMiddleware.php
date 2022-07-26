<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use lgdz\hyperf\Tools;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EncryptMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        isset($body['encrypt']) && Tools::Encrypt($body['encrypt'], function ($value, $offset, $length) use ($request) {
            switch ($request->getMethod()) {
                case 'POST':
                case 'PUT':
                case 'DELETE':
                    $request = $request->withParsedBody(
                        json_decode(Tools::F()->encrypt->decode($value, $offset, $length), true)
                    );
                    \Hyperf\Utils\Context::set(ServerRequestInterface::class, $request);
                    break;
            }
        });

        return $handler->handle($request);
    }
}