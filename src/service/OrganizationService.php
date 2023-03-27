<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Config\Annotation\Value;
use Hyperf\DbConnection\Db;
use lgdz\object\Body;
use lgdz\object\Query;
use lgdz\hyperf\model\{Account, Organization, OrganizationGrade, User};
use lgdz\hyperf\Tools;

class OrganizationService
{
    /**
     * @Value("lgdz.organization")
     */
    private $config;

    public function index(Query $input)
    {
        $list = Organization::query()->with('grade.role')->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->grade_id, function ($query, $value) {
            return $query->where('grade_id', $value);
        })->when($input->keyword, function ($query, $value) {
            return $query->where(function ($query) use ($value) {
                $value = '%' . $value . '%';
                $query->where('name', 'like', $value)->orWhere('full_name', 'like', $value);
            });
        })->when($input->pid, function ($query, $value) {
            return $query->whereRaw("find_in_set({$value},path)");
        })->orderByRaw('len asc,sort asc,id asc')->get()->toArray();
        return empty($list) ? [] : Tools::F()->tree->builds($list, array_column($list, 'pid'));
    }

    public function create(Body $input)
    {
        $org = new Organization();
        $org->setFormData($input);

        Db::transaction(function () use ($org) {
            $org->save();
            // 生成path
            $org->savePath();

            // 创建管理员账号
            if (isset($this->config['create_default_user']) && $this->config['create_default_user'] === true) {
                Tools::Service()->account->create(new Body([
                    'org_id' => $org->id,
                    'role_id' => $org->grade->admin_role_id,
                    'username' => $org->name,
                    'password' => sprintf('%s@123456', ucfirst(Tools::F()->pinyin->initial($org->name))),
                    'realname' => '',
                    'phone' => '',
                    'from_id' => Tools::Org()->id // 账号来源
                ]));
            }
        });

    }

    public function update(int $id, Body $input)
    {
        $this->checkPermissionDomain($this->org($this->findById($id)), function (Organization $org) use ($input) {
            // 修改前的组织类型
            $old_grade_id = $org->grade_id;
            $old_pid = $org->pid;

            $org->setFormData($input);
            // 修改后的组织类型
            $new_grade_id = $org->grade_id;
            $new_pid = $org->pid;

            if ($old_grade_id !== $new_grade_id) {
                Tools::E('组织架构只能同级别移动');
            }

            $org->save();

            // 生成path
            $org->savePath();
            // 更新了pid
            if ((int)$new_pid !== $old_pid) {
                $this->updateChild($org->id);
            }
        });
    }

    public function patchExtends(int $id, Body $input)
    {
        $this->checkPermissionDomain($this->org($this->findById($id)), function (Organization $org) use ($input) {
            $org->extends = $input->body;
            $org->save();
        });
    }

    // 更新子级path
    private function updateChild(int $id)
    {
        Organization::query()->where('pid', $id)->get()->each(function (Organization $org) {
            $org->savePath();
            $this->updateChild($org->id);
        });
    }

    public function delete(int $id)
    {
        $this->checkPermissionDomain($this->org($this->findById($id)), function (Organization $org) {
            // 验证是否有下级组织
            if (Organization::query()->where('pid', $org->id)->exists()) {
                Tools::E('有子级禁止删除');
            }
            Db::transaction(function () use ($org) {
                $org->delete();
                Account::query()->where('org_id', $org->id)->delete();
            });
        });
    }

    // 检查是否有对组织进行操作权限，有则在闭包中完成操作业务
    public function checkPermissionDomain(Organization $org, \Closure $callback)
    {
        if (in_array(Tools::Org()->id, $org->pids)) {
            return $callback($org);
        }
    }

    public function findById(int $id)
    {
        return Organization::findFromCache($id);
    }

    /**
     * 验证参数是否是Organization对象，如不是抛出异常
     * @param $org
     * @return Organization
     */
    public function org($org): Organization
    {
        return ($org instanceof Organization) ? $org : Tools::E('组织不存在');
    }

    public function select(int $org_id = 0, bool $self = true)
    {
        if (!$org_id) {
            $org_id = Tools::Org()->id;
        }
        $model = Organization::query()->whereRaw("find_in_set({$org_id},path)");
        $list = $model->orderByRaw('len asc,sort asc,id asc')->get()->toArray();
        if (empty($list)) {
            return [];
        } else {
            $tree = Tools::F()->tree->build($list, $list[0]['pid']);
            if (!$self) {
                $tree = $tree[0]['children'] ?? [];
            }
            return $tree;
        }
    }
}