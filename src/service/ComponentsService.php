<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Di\Annotation\Inject;
use lgdz\hyperf\Tools;

class ComponentsService
{
    /**
     * @Inject()
     * @var DictionaryService
     */
    protected $DictionaryService;

    /**
     * @Inject()
     * @var AuthService
     */
    protected $AuthService;

    /**
     * @Inject()
     * @var RoleService
     */
    protected $RoleService;

    /**
     * @Inject()
     * @var LoginLogService
     */
    protected $LoginLogService;

    // 最近登录列表
    public function LoginLog(int $limit = 10)
    {
        return $this->LoginLogService->index([
            'user_id' => Tools::U()->id,
            'limit'   => $limit
        ]);
    }

    // 数据字典
    public function Dictionary(string $name)
    {
        return $this->DictionaryService->get($name);
    }

    // 权限列表
    public function RuleAll(int $role_id)
    {
        $rule_list = $this->AuthService->getRoleRules(
            $this->RoleService->read($role_id)
        );
        return empty($rule_list) ? [] : Tools::$factory->Tree()->tree($rule_list, $rule_list[0]['pid']);
    }

    // 角色列表
    public function RoleSelect(bool $self)
    {
        $role_id = $self ? Tools::U()->parent_role_id : Tools::U()->role_id;
        $list = $this->RoleService->index([
            'pid' => $role_id
        ])->toArray();
        return Tools::$factory->Tree()->tree($list, $role_id);
    }

    // 数据重置类型
    public function ResetData()
    {
        return ['用户', '角色', '设置', '操作日志', '登录日志', '文件记录', '检查项目分类', '检查项目类型', '数据字典', '知识库'];
    }

    // 组织架构
    public function Organization(bool $get_disabled = true, int $type = 1)
    {
        switch ($type) {
            case 1:
                $id = 0;
                break;
            case 2:
                $id = Tools::U()->agency_id;
                break;
            case 3:
                $id = Tools::U()->org_id;
                break;
        }
        return $this->OrganizationService->index(['get_disabled' => $get_disabled, 'path' => $id]);
    }

    // 根组织
    public function RootOrganization(int $type = 1)
    {
        switch ($type) {
            case 1:
                $id = 0;
                break;
            case 2:
                $id = Tools::U()->agency_id;
                break;
        }
        return $this->OrganizationService->index(['grid_level_id' => 1, 'id' => $id]);
    }

    // 检查项目分类
    public function CheckCategory()
    {
        return array_map(function ($row) {
            return [
                'label' => $row->name,
                'value' => $row->id
            ];
        }, $this->CheckCategoryService->index()->all());
    }

    // 检查项目类型树
    public function CheckTypeTree(bool $disable_checkbox = false)
    {
        return $this->CheckTypeService->tree($disable_checkbox);
    }

    // 网格场所
    public function GridPlace(int $org_id)
    {
        return $this->PlaceService->index(['org_id' => $org_id]);
    }

    // 网格区域
    public function GridArea()
    {
        return $this->AreaService->tree();
    }
}