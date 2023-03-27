### 快速生成增删改查服务类
- args[服务类名 数据库模型]
`php bin/hyperf.php lgdz:service TestService App\\Model\\Test`

### 快速生成增删改查控制器
- args[控制器名 路由地址 服务类]

`php bin/hyperf.php lgdz:controller TestController /test App\\Service\\TestService`