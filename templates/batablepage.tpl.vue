<template>
    <!-- baTable 标准 CRUD 页面 -->
    <!-- 所有配置通过 column 驱动，禁止手写 el-table / el-form-item -->
    <div class="default-main ba-table-box">
        <el-alert
            class="ba-table-alert"
            v-if="baTable.table.remark"
            :title="baTable.table.remark"
            type="info"
            show-icon
        />

        <!-- 顶部工具栏 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('Title / Name') })"
        />

        <!-- 数据表格（由 baTable column 配置自动渲染） -->
        <Table />

        <!-- 新增/编辑弹窗（由 baTable column 配置自动生成表单） -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue'
import { useI18n } from 'vue-i18n'
import Table from '/@/components/table/index.vue'
import PopupForm from './popupForm.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { baTableClass } from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table/index'
import { baTableApi } from '/@/api/common'

const { t } = useI18n()

// 初始化 baTable 实例
// url 对应后端控制器的路由地址（通常为 /admin/模块名/列表名）
const baTable = new baTableClass(
    new baTableApi('/admin/[module]/[controller]/'),
    {
        // 表格列配置（驱动前端渲染）
        column: [
            { type: 'selection', fixed: 'left', operator: false },
            { label: t('id'), prop: 'id', width: 70, sortable: 'custom', operator: '=' },

            // 普通文本列
            { label: t('Title'), prop: 'title', operator: 'LIKE', width: 200 },

            // 状态列：自动渲染 Tag 标签
            {
                label: t('Status'),
                prop: 'status',
                width: 100,
                render: 'tag',
                replaceValue: { '0': t('Disabled'), '1': t('Enabled') },
                operator: '=',
                operatorPlaceholder: t('Please select'),
            },

            // 图片列：自动渲染图片缩略图
            { label: t('Cover'), prop: 'cover_image', render: 'image', operator: false },

            // 时间列：支持范围筛选
            {
                label: t('Create time'),
                prop: 'create_time',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },

            // 操作列：固定在右侧
            {
                label: t('Operate'),
                align: 'center',
                width: 140,
                render: 'buttons',
                buttons: defaultOptButtons(['edit', 'delete']),
                operator: false,
            },
        ],
    },
    {
        // 默认排序
        defaultOrder: { prop: 'id', order: 'desc' },
    }
)

// 将 baTable 实例注入子组件（PopupForm 等）
provide('baTable', baTable)

// 挂载并加载数据
baTable.mount()
baTable.getList()
</script>
