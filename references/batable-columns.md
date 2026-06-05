# baTable Column 配置完整参数字典

> 本文档列出 baTable `column` 数组中每个列对象的常用配置项，供快速查阅。

---

## 基础字段配置

| 属性 | 类型 | 说明 |
| :--- | :--- | :--- |
| `label` | `string` | 列标题（显示在表头） |
| `prop` | `string` | 后端字段名（必须与接口返回字段一致） |
| `width` | `number` | 列宽（单位 px） |
| `fixed` | `'left'` \| `'right'` | 固定列方向 |
| `align` | `'left'` \| `'center'` \| `'right'` | 对齐方式 |
| `sortable` | `'custom'` \| `false` | 是否启用排序 |
| `show` | `boolean` | 是否默认显示（可被用户关闭） |

---

## render 渲染类型

| `render` 值 | 效果 | 配套属性 |
| :--- | :--- | :--- |
| `'tag'` | 渲染为 el-tag 标签 | `replaceValue`、`custom` |
| `'image'` | 渲染为图片缩略图 | 无 |
| `'images'` | 渲染为多图缩略图列表 | 无 |
| `'datetime'` | 格式化 Unix 时间戳为日期时间 | 无 |
| `'date'` | 格式化 Unix 时间戳为日期 | 无 |
| `'switch'` | 渲染为快捷切换开关 | 需后端支持单字段更新接口 |
| `'buttons'` | 渲染为操作按钮组 | `buttons` 属性 |
| `'icon'` | 渲染为图标预览 | 无 |
| `'url'` | 渲染为可点击链接 | 无 |

---

## replaceValue 枚举映射

用于将数据库中的原始值替换为可读文案（配合 `render: 'tag'` 使用）：

```typescript
{
    prop: 'status',
    render: 'tag',
    replaceValue: {
        '0': '禁用',
        '1': '启用',
        '2': '待审核',
    },
    // 可以配合 custom 自定义 tag 类型
    custom: {
        '0': { type: 'danger' },
        '1': { type: 'success' },
        '2': { type: 'warning' },
    }
}
```

---

## operator 筛选操作符

控制该列在搜索栏中的筛选方式：

| `operator` 值 | 说明 |
| :--- | :--- |
| `'LIKE'` | 模糊搜索（适用于文本字段） |
| `'='` | 精确匹配（适用于 ID、枚举字段） |
| `'!='` | 不等于 |
| `'>'` / `'<'` | 大于/小于 |
| `'>='` / `'<='` | 大于等于/小于等于 |
| `'RANGE'` | 范围筛选（适用于日期、数字字段，生成双输入框） |
| `'IN'` | 包含（生成多选 Select） |
| `false` | 不生成筛选项 |

---

## buttons 操作按钮

`render: 'buttons'` 时，通过 `buttons` 属性配置操作按钮：

```typescript
import { defaultOptButtons } from '/@/components/table/index'

// 使用预设按钮（edit = 编辑，delete = 删除）
buttons: defaultOptButtons(['edit', 'delete'])

// 自定义按钮
buttons: [
    {
        name: 'publish',
        title: '发布',
        type: 'primary',
        icon: 'el-icon-upload',
        // 按钮权限控制（对应后端控制器方法名）
        auth: ['admin/Article/publish'],
        // 点击时是否需要确认
        popconfirm: { title: '确认发布该文章？' },
    }
]
```

---

## 特殊列类型

```typescript
// 复选框列（必须放在最前面）
{ type: 'selection', fixed: 'left', operator: false }

// 展开行列
{ type: 'expand', operator: false }

// 序号列
{ type: 'index', label: '序号', width: 60, operator: false }
```

---

## 完整示例

```typescript
column: [
    { type: 'selection', fixed: 'left', operator: false },
    { label: 'ID', prop: 'id', width: 70, sortable: 'custom', operator: '=' },
    { label: '标题', prop: 'title', operator: 'LIKE', width: 200 },
    { label: '分类', prop: 'category_id', render: 'tag',
      replaceValue: { '1': '新闻', '2': '公告', '3': '教程' }, operator: '=' },
    { label: '状态', prop: 'status', render: 'tag',
      replaceValue: { '0': '禁用', '1': '启用' },
      custom: { '0': { type: 'danger' }, '1': { type: 'success' } },
      operator: '=' },
    { label: '封面', prop: 'cover_image', render: 'image', operator: false },
    { label: '置顶', prop: 'is_top_switch', render: 'switch', operator: false },
    { label: '创建时间', prop: 'create_time', render: 'datetime',
      sortable: 'custom', operator: 'RANGE', width: 160 },
    { label: '操作', align: 'center', width: 160, render: 'buttons',
      buttons: defaultOptButtons(['edit', 'delete']), operator: false },
]
```
