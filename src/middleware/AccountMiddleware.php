<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\hyperf\service\OrganizationService;
use lgdz\hyperf\service\AccountService;
use lgdz\hyperf\Tools;

class AccountMiddleware implements MiddlewareInterface
{

    /**
     * @Value("lgdz.account")
     */
    private $config;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $account_id = $request->getHeaderLine('accountid');
        if (empty($account_id)) {
            Tools::E('accountid未定义');
        }

        // 身份信息保存到上下文
        $this->setAccount((int)$account_id);
        // 组织信息保存到上下文
        $this->setOrg(Tools::Account()->org_id);
        // 扩展回调
        $this->callback($request);
        return $handler->handle($request);
    }

    private function setAccount(int $account_id)
    {
        $account = Tools::Service()->account->account(Tools::Service()->account->findById($account_id));
        if ($account->user_id !== Tools::U()->id) {
            Tools::E('非法操作');
        }
        Tools::Account($account);
    }

    private function setOrg(int $org_id)
    {
        $org = Tools::Service()->organization->org(Tools::Service()->organization->findById($org_id));
        Tools::Org($org);
    }

    private function callback(ServerRequestInterface $request)
    {
        if (!$this->config['callback_enable']) {
            return;
        }
        $router = $request->getAttribute(Dispatched::class)->handler->route ?? null;
        if (in_array($router, $this->config['callback_free_router'])) {
            return;
        }
        $method = $this->config['callback_method'];
        $this->config['callback_class']::$method();
    }
}