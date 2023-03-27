<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Di\Annotation\Inject;
use lgdz\Factory;
use lgdz\hyperf\service\AccountService;
use lgdz\hyperf\service\FileCategoryService;
use lgdz\hyperf\service\FileService;
use lgdz\hyperf\service\OrganizationService;
use lgdz\hyperf\service\UserService;
use Psr\EventDispatcher\EventDispatcherInterface;

class Service
{
    /**
     * @Inject()
     * @var EventDispatcherInterface
     */
    public $event;

    /**
     * @Inject(lazy=true)
     * @var UserService
     */
    public $user;

    /**
     * @Inject(lazy=true)
     * @var AccountService
     */
    public $account;

    /**
     * @Inject(lazy=true)
     * @var OrganizationService
     */
    public $organization;

    /**
     * @Inject(lazy=true)
     * @var OrganizationGradeService
     */
    public $organizationGrade;

    /**
     * @Inject(lazy=true)
     * @var DepartmentService
     */
    public $department;

    /**
     * @Inject(lazy=true)
     * @var DepartmentMemberService
     */
    public $departmentMember;

    /**
     * @Inject(lazy=true)
     * @var AuthService
     */
    public $auth;

    /**
     * @Inject(lazy=true)
     * @var LoginLogService
     */
    public $loginLog;

    /**
     * @Inject(lazy=true)
     * @var RoleService
     */
    public $role;

    /**
     * @Inject(lazy=true)
     * @var FileCategoryService
     */
    public $fileCategory;

    /**
     * @Inject(lazy=true)
     * @var FileService
     */
    public $file;

    /**
     * @Inject(lazy=true)
     * @var Factory
     */
    public $factory;
}