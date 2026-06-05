<?php

namespace app\admin\controller\[MODULE];

use app\common\controller\Backend;
use app\admin\model\[ModelName];

/**
 * [功能模块名称]管理
 */
class [ControllerName] extends Backend
{
    /**
     * 绑定对应 Model
     * 注意：不要在类顶部声明 $model 属性（父类已有），在此赋值即可
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new [ModelName]();

        // 过滤不对外暴露的敏感字段
        $this->preExcludeFields = ['create_time', 'update_time'];

        // 可快速搜索的字段（支持模糊搜索）
        $this->quickSearchField = ['title', 'name'];
    }

    /**
     * 自定义 index 逻辑示例
     * 如果不需要自定义，直接删除此方法，由 Trait 自动处理
     * 注意：必须保留 : void 返回值类型
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            // 下拉选择模式（用于关联字段远程搜索）
            $this->model->withoutField($this->preExcludeFields)->select();
            parent::index();
            return;
        }
        parent::index();
    }

    /**
     * 自定义 add 逻辑示例
     * 注意：必须保留 : void 返回值类型
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 在此处添加自定义前置处理
            // 例如：$data['user_id'] = $this->auth->id;
        }
        parent::add();
    }

    /**
     * 自定义 edit 逻辑示例
     * 注意：必须保留 : void 返回值类型
     */
    public function edit(): void
    {
        parent::edit();
    }

    /**
     * 自定义操作方法示例（带权限节点）
     * 前端 v-auth="'publish'" 指令将自动识别此方法为权限节点
     */
    public function publish(): void
    {
        $ids = $this->request->post('ids/a', []);
        if (empty($ids)) {
            $this->error('请选择要操作的记录');
        }

        try {
            [ModelName]::whereIn('id', $ids)->update(['status' => 1]);
            $this->success('发布成功');
        } catch (\think\exception\HttpResponseException $e) {
            throw $e; // 必须重新抛出，交还给框架渲染响应
        } catch (\Throwable $e) {
            $this->error('操作失败：' . $e->getMessage());
        }
    }
}
