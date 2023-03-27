<?php

declare (strict_types=1);

namespace lgdz\hyperf;

use Closure;
use Hyperf\HttpMessage\Stream\SwooleStream;
use lgdz\hyperf\model\Account;
use lgdz\hyperf\model\Organization;
use lgdz\hyperf\model\OrganizationGrade;
use lgdz\hyperf\model\User;
use lgdz\hyperf\service\OplogService;
use lgdz\hyperf\service\OrganizationService;
use lgdz\hyperf\service\Service;
use lgdz\object\Body;
use lgdz\object\Query;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Hyperf\Utils\Context;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use lgdz\Factory;
use lgdz\lib\HttpResponse;
use lgdz\hyperf\exception\BusinessException;

/**
 * Class Tools 助手
 * @package lgdz\hyperf
 */
class Tools
{
    protected static $encrypt = null;

    // 字典缓存容器
//    protected static $dictionary = ['id_index_list' => [], 'name_index_tree' => []];

    // 更新字典缓存
    public static function D2Cache(array $id_index_list, array $name_index_tree)
    {
        Tools::C_set('dictionary:id_index_list', $id_index_list);
        Tools::C_set('dictionary:name_index_tree', $name_index_tree);
//        static::$dictionary['id_index_list'] = $id_index_list;
//        static::$dictionary['name_index_tree'] = $name_index_tree;
    }

    // 获取字典中文名称
    public static function D2Label($value, string $name)
    {
        $key = 'dictionary:id_index_list';
        $data = static::Context($key);
        if (is_null($data)) {
            $data = Tools::C_get($key);
            static::Context($key, $data);
        }
        return $data[sprintf('%s_%s', $name, $value)] ?? '-';
//        return static::$dictionary['id_index_list'][$id] ?? '-';
    }

    // 获取字典树结构数据
    public static function D2Tree(string $name)
    {
        return static::D2TreeAll()[$name] ?? [];
//        return static::$dictionary['name_index_tree'][$name] ?? [];
    }

    // 获取字典数结构数据（全部）
    public static function D2TreeAll()
    {
        $key = 'dictionary:name_index_tree';
        $data = static::Context($key);
        if (is_null($data)) {
            $data = Tools::C_get($key);
            static::Context($key, $data);
        }
        return $data;
    }

    /**
     * 用户信息
     * @param User|null $user
     * @return User
     */
    public static function U(User $user = null): User
    {
        if (is_null($user)) {
            return Context::get('user');
        } else {
            Context::set('user', $user);
            return $user;
        }
    }

    /**
     * 数据库查询分页
     * @param PaginatorInterface $paginate
     * @param Closure|null $closure
     * @return array
     */
    public static function P(PaginatorInterface $paginate, Closure $closure = null): array
    {
        return [
            'total' => $paginate->total(),
            'pages' => $paginate->lastPage(),
            'items' => is_null($closure) ? $paginate->items() : array_map($closure, $paginate->items())
        ];
    }

    /**
     * 输入
     * @return RequestInterface|mixed
     */
    public static function I()
    {
        return ApplicationContext::getContainer()->get(RequestInterface::class);
    }

    /**
     * 输出
     * @return ResponseInterface|mixed
     */
    public static function O()
    {
        return ApplicationContext::getContainer()->get(ResponseInterface::class);
    }

    /**
     * 响应JSON
     * @return HttpResponse
     */
    public static function R(): HttpResponse
    {
        return static::F()->response;
    }

    /**
     * 缓存控制对象
     * @return mixed|\Psr\SimpleCache\CacheInterface
     */
    public static function C()
    {
        return ApplicationContext::getContainer()->get(\Psr\SimpleCache\CacheInterface::class);
    }

    /**
     * 缓存设置
     * @param string $key
     * @param $value
     * @param null $ttl
     * @return bool
     */
    public static function C_set(string $key, $value, $ttl = null): bool
    {
        try {
            return static::C()->set($key, $value, $ttl);
        } catch (InvalidArgumentException $e) {
            static::E($e->getMessage());
        }
    }

    /**
     * 缓存读取
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function C_get($key, $default = null)
    {
        try {
            return static::C()->get($key, $default);
        } catch (InvalidArgumentException $e) {
            static::E($e->getMessage());
        }
    }

    /**
     * 异常抛出
     * @param string $msg
     * @return mixed
     */
    public static function E(string $msg)
    {
        throw new BusinessException($msg);
    }

    /**
     * 初始化Http加密配置
     * @return array
     */
    public static function Encrypt()
    {
        if (is_null(static::$encrypt)) {
            static::$encrypt = config('lgdz.encrypt');
        }
        return static::$encrypt;
    }

    /**
     * 接口正常返回
     * @param null $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function Ok($data = null)
    {
        $config = static::Encrypt();
        if ($config['enable']) {
            $data = Tools::F()->encrypt->encode(json_encode($data), $config['offset'], $config['length']);
        }
        return static::O()->json(
            static::R()->ok($data)
        );
    }

    /**
     * 接口异常返回
     * @param string $message
     * @param int $status
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function Bad(string $message = 'Error', int $status = 0)
    {
        return static::O()->json(
            static::R()->bad($message, $status)
        );
    }

    /**
     * 工具类
     * @return Factory
     */
    public static function F()
    {
        return Factory::container();
    }

    /**
     * 文件日志操作对象
     * @param string $name
     * @return LoggerInterface
     */
    public static function Log(string $name = 'log')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name);
    }

    /**
     * 记录操作日志
     * @param string $title
     */
    public static function Oplog(string $title = '')
    {
        OplogService::create($title);
    }

    public static function Query(array $append = [])
    {
        $query = new Query(Tools::I()->getQueryParams());
        if (!empty($append)) {
            foreach ($append as $key => $value) {
                $query->$key = $value;
            }
        }
        return $query;
    }

    public static function Body(array $append = [])
    {
        $body = new Body(Tools::I()->getParsedBody());
        if (!empty($append)) {
            foreach ($append as $key => $value) {
                $body->$key = $value;
            }
        }
        return $body;
    }

    public static function container()
    {
        return ApplicationContext::getContainer();
    }

    /**
     * @param Account|null $account
     * @return Account
     */
    public static function Account(Account $account = null): Account
    {
        if (is_null($account)) {
            return Context::get('account');
        } else {
            Context::set('account', $account);
            return $account;
        }
    }

    /**
     * @param Organization|null $org
     * @return Organization
     */
    public static function Org(Organization $org = null): Organization
    {
        if (is_null($org)) {
            return Context::get('org');
        } else {
            Context::set('org', $org);
            return $org;
        }
    }

    /**
     * 组织ID获取组织信息
     * @param int $id
     * @return Organization
     */
    public static function OrgById(int $id): Organization
    {
        $service = static::container()->get(OrganizationService::class);
        return $service->org($service->findById($id));
    }

    /**
     * 判断当前是否是目标组织的领导组织(包含本组织)
     * @param int $target_org_id
     * @return bool
     */
    public static function IsTargetParentOrg(int $target_org_id): bool
    {
        $org = static::Service()->organization->org(static::Service()->organization->findById($target_org_id));
        return static::Org()->id === $target_org_id || in_array(static::Org()->id, $org->pids);
    }

    /**
     * 获取组织默认管理员角色ID
     * @param int $org_id
     * @return int
     */
    public static function DefaultOrgAdminRoleId(int $org_id): int
    {
        $org_service = Tools::container()->get(OrganizationService::class);
        $org = $org_service->org($org_service->findById($org_id));
        return (int)OrganizationGrade::query()->where('id', $org->grade_id)->value('admin_role_id');
    }

    public static function Context(string $name, $value = null)
    {
        if (is_null($value)) {
            return Context::get($name);
        } else {
            Context::set($name, $value);
        }
    }

    /**
     * 资源保存路径
     * @param string $type
     * @param string $filename
     * @return string
     */
    public static function StoragePath(string $type, string $filename)
    {
        $dirs = sprintf('%s/%s/', $type, date('Y-m-d'));
        !is_dir(static::AbsPath($dirs)) && mkdir(static::AbsPath($dirs), 0777, true);
        return $dirs . $filename;
    }

    /**
     * 资源本地绝对路径
     * @param string $path
     * @return string
     */
    public static function AbsPath(string $path)
    {
        return BASE_PATH . '/storage/' . $path;
    }

    /**
     * @param string $downloadName 下载文件名称
     * @param string $stream 文件内容
     * @return mixed
     */
    public static function ResponseXlsx(string $downloadName, string $stream)
    {
        return static::O()->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('content-disposition', "attachment; filename={$downloadName}.xlsx")
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream($stream));
    }

    /**
     * 数据结果缓存
     * @param string $key
     * @param int $ttl
     * @param Closure $loadData
     * @param bool $force
     * @return int|string|array
     */
    public static function CacheData(string $key, \Closure $loadData, int $ttl = 7200, bool $force = false)
    {
        $time = time();
        $data = static::C_get($key);
        if (!$data || $force) {
            $data['data'] = $loadData();
            $data['expire'] = $time + $ttl;
            static::C_set($key, $data);
        } elseif (isset($data['expire']) && $data['expire'] < $time) {
            go(function () use ($key, $ttl, $time, $loadData) {
                $data['data'] = $loadData();
                $data['expire'] = $time + $ttl;
                static::C_set($key, $data);
            });
        }
        return $data['data'];
    }

    /**
     * 生成数据库排序字段值
     * @param Query $query
     * @param string $default
     * @return string
     */
    public static function BuildOrder(Query $query, string $default = 'id')
    {
        $order_methods = [
            'ascend' => 'asc',
            'descend' => 'desc'
        ];
        if ($query->sortField && $query->sortOrder) {
            return "{$query->sortField} {$order_methods[$query->sortOrder]}";
        } else {
            return "{$default} desc";
        }
    }

    /**
     * 批量删除处理
     * @param string $id
     * @param Closure $handle
     * @return array
     */
    public static function Dels(string $id, \Closure $handle): array
    {
        $ids = explode(',', $id);
        $success = 0;
        $error = 0;
        $errorMsg = [];
        foreach ($ids as $id) {
            try {
                $handle((int)$id);
                $success++;
            } catch (\Throwable $e) {
                $error++;
                $errorMsg[] = $e->getMessage();
            }
        }
        return ['message' => sprintf('成功删除%d条记录', $success), 'success' => $success, 'error' => $error, 'errorMsg' => $errorMsg];
    }

    public static function OwnOrgCheck(int $org_id, string $msg = '非组织成员')
    {
        static::Org()->id !== $org_id && static::E($msg);
    }

    public static function IsBelongOrg(int $org_id): bool
    {
        return static::Org()->id === $org_id;
    }

    public static function Service()
    {
        return static::container()->get(Service::class);
    }
}