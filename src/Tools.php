<?php

declare (strict_types=1);

namespace lgdz\hyperf;

use Closure;
use Hyperf\Contract\PaginatorInterface;

/**
 * Class Tools 工具类
 * @package lgdz\hyperf
 */
class Tools
{
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
}