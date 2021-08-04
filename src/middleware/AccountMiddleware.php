<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Di\Annotation\Inject;
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
     * @Inject()
     * @var OrganizationService
     */
    protected $OrgService;

    /**
     * @Inject()
     * @var AccountService
     */
    protected $AccountService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $account_id = $request->getHeaderLine('account_id');
        if (empty($account_id)) {
            Tools::E('account_id未定义');
        }

        // 身份信息保存到上下文
        $this->setAccount((int)$account_id);
        // 组织信息保存到上下文
        $this->setOrg(Tools::Account()->org_id);
        return $handler->handle($request);
    }

    private function setAccount(int $account_id)
    {
        $account = $this->AccountService->account($this->AccountService->findById($account_id));
        if ($account->user_id !== Tools::U()->id) {
            Tools::E('非法操作');
        }
        Tools::Account($account);
    }

    private function setOrg(int $org_id)
    {
        $org = $this->OrgService->org($this->OrgService->findById($org_id));
        Tools::Org($org);
    }
}