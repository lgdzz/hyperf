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
        $config = Tools::Encrypt();
        if ($config['enable']) {
            $body = $request->getParsedBody();
            switch ($request->getMethod()) {
                case 'POST':
                case 'PUT':
                case 'DELETE':

                    if (isset($body['encrypt'])) {
                        $request = $request->withParsedBody(
                            json_decode(Tools::F()->encrypt->decode($body['encrypt'], $config['offset'], $config['length']), true)
                        );
                        \Hyperf\Utils\Context::set(ServerRequestInterface::class, $request);
                    }

                    break;
            }
        }

        return $handler->handle($request);
    }
}