<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\service\ComponentsService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares(
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class)
 * )
 */
class ComponentsController
{
    protected $apis;

    public function __construct()
    {
        $api = config('lgdz.component_api');
        if (is_null($api) || !class_exists($api)) {
            $this->apis = new ComponentsService;
        } else {
            $this->apis = new $api;
        }
    }

    /**
     * @RequestMapping(path="/l/component", methods="post")
     */
    public function index()
    {
        $body = Tools::Body();
        $methods = $body->methods;
        array_walk($methods, function (&$value) {
            if (is_null($value)) {
                $args = [];
            } elseif (is_array($value)) {
                $args = $value;
            } else {
                $args = [$value];
            }
            $value = $args;
        });
        $result = $this->api($methods);
        return Tools::Ok($result['components']);
    }

    protected function api(array $methods)
    {
        $components = [];
        if (empty($methods)) {
            return ['components' => []];
        }
        foreach ($methods as $op => $args) {
            $args = is_null($args) ? [] : (is_array($args) ? $args : [$args]);
            if (!$op) {
                continue;
            } else {
                try {
                    if (!method_exists($this->apis, $op))
                        Tools::E("组件中中没有找到{$op}方法");
                    else
                        $components[$op] = $this->apis->$op(...$args);
                } catch (\Throwable $e) {
                    $components[$op] = $e->getMessage();
                }
            }
        }
        return ['components' => $components];
    }
}