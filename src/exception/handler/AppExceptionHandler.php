<?php

declare(strict_types=1);

namespace lgdz\hyperf\exception\handler;

use lgdz\hyperf\exception\BusinessException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use lgdz\exception\JwtAuthException;
use lgdz\hyperf\Tools;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();

        if ($throwable instanceof ValidationException) {
            // 表单验证不通过响应
            return $this->response($response, $throwable->validator->errors()->first(), 200);
        } elseif ($throwable instanceof JwtAuthException) {
            // 身份验证不通过响应
            return $this->response($response, $throwable->getMessage(), 401);
        } elseif ($throwable instanceof BusinessException) {
            // 业务异常
            return $this->response($response, $throwable->getMessage(), 200);
        } else {
            // 其他异常
            return $this->response($response, $throwable->getMessage(), 200);
        }
    }

    private function response(ResponseInterface $response, string $message, int $status)
    {
        $data = json_encode(Tools::R()->bad($message), JSON_UNESCAPED_UNICODE);
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8')->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
