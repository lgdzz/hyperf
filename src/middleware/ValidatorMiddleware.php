<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\hyperf\Tools;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\validator\ValidatorInterface;

class ValidatorMiddleware implements MiddlewareInterface
{
    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    // 验证器
    protected $validators = null;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取控制器类名与方法名
        $callback = $request->getAttribute(Dispatched::class)->handler->callback;
        // 获取验证器
        $validator = $this->getValidator(...$callback);
        if ($validator) {
            // 实例化验证器
            $class = new $validator;
            // 验证表单数据
            $this->check($request->getParsedBody(), $callback[1], $class);
        }
        return $handler->handle($request);
    }

    public function check(array $data, string $scene, ValidatorInterface $validator)
    {
        // 执行自定义验证处理
        $validator->custom($data, $scene);
        // 执行自动验证处理
        $result = $this->validationFactory->make($data, $validator->rule(), $validator->message());
        if ($result->fails()) {
            Tools::E($result->errors()->first());
        }
    }

    private function getValidator(string $controller, string $method)
    {
        if (is_null($this->validators)) {
            $validators = AnnotationCollector::getMethodsByAnnotation(Validator::class);
            foreach ($validators as $validator) {
                $this->validators[$this->key($validator['class'], $validator['method'])] = $validator['annotation']->validator;
            }
        }
        return $this->validators[$this->key($controller, $method)] ?? null;
    }

    private function key(string $controller, string $method)
    {
        return md5($controller . $method);
    }
}