<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Config\Annotation\Value;
use lgdz\hyperf\Tools;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EncryptMiddleware implements MiddlewareInterface
{

    /**
     * @Value("lgdz.encrypt")
     */
    private $config;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->config['enable']) {
            $body = $request->getParsedBody();
            switch ($request->getMethod()) {
                case 'POST':
                case 'PUT':
                case 'DELETE':

                    if (isset($body['encrypt'])) {
                        $request = $request->withParsedBody(
                            json_decode(Tools::Service()->factory->encrypt->decode($body['encrypt'], $this->config['offset'], $this->config['length']), true)
                        );
                        \Hyperf\Utils\Context::set(ServerRequestInterface::class, $request);
                    }

                    break;
            }
        }

        return $handler->handle($request);
    }
}