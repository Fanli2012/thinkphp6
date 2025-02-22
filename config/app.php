<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['common'],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '服务器内部错误HTTP-Internal Server Error',
    // 显示错误信息
    'show_error_msg'   => false,

	//等级推荐
    "tuijian"               => [
        "0"=>"不推荐",
        "1"=>"一级推荐",
        "2"=>"二级推荐",
        "3"=>"三级推荐",
        "4"=>"四级推荐"
    ],

	//密码加盐
    'password_salt'         => '123456',

];
