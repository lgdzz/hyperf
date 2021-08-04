<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\hyperf\service\OrganizationService;
use lgdz\hyperf\Tools;

class OrgMiddleware implements MiddlewareInterface
{
    /**
     * @Inject()
     * @var OrganizationService
     */
    protected $OrgService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $org_id = $request->getHeaderLine('org_id');
        if (empty($org_id)) {
            Tools::E('org_id未定义');
        }

        // 站点信息保存到上下文
        $this->setOrg((int)$org_id);
        return $handler->handle($request);
    }

    private function setOrg(int $org_id)
    {
        $org = $this->OrgService->org($this->OrgService->findById($org_id));
        Tools::Org($org);
    }
}