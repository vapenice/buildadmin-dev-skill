<?php

namespace app\admin\validate;

use think\Validate;

/**
 * [功能模块名称]验证器
 * 字段校验逻辑统一放在 Validate 类中，禁止在 Controller 中写 if 校验
 */
class [ValidateName] extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'title'  => 'require|maxLength:200',
        'status' => 'require|in:0,1',
        'type'   => 'require|integer',
    ];

    /**
     * 字段中文提示（用于错误信息）
     */
    protected $field = [
        'title'  => '标题',
        'status' => '状态',
        'type'   => '类型',
    ];

    /**
     * 验证场景定义
     * add 场景：新增时的必填/校验规则
     * edit 场景：编辑时可放开某些字段
     */
    protected $scene = [
        'add'  => ['title', 'status', 'type'],
        'edit' => ['title', 'status'],
    ];
}
