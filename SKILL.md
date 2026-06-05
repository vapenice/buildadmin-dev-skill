---
name: buildadmin-dev
description: |
  BuildAdmin 二次开发核心规范与安全规约。
  适用于所有基于 BuildAdmin (ThinkPHP 6 + Vue 3) 框架进行二次开发的场景。
  覆盖后端 Admin/API 控制器、ThinkORM、数据库设计、前端 baTable CRUD、TypeScript 类型安全、前端 UI/UX 交互规范，以及常见致命错误的规避 SOP。
  激活时机：新建或修改 Controller、Model、Validate、Vue CRUD 页面、API 接口定义，或遇到棘手问题需要查阅 BuildAdmin 官方资源时。
---

# BuildAdmin 二次开发技能指令集

> 本技能包定义了 BuildAdmin 二次开发的核心约束、高频陷阱规避指南与调试 SOP。
> 在创建或修改任何 Controller / Model / Validate / Vue 页面文件之前，必须遵守本规约。

---

## 零、遇到棘手问题时的搜索策略

当遇到无法通过本 SKILL 规范解决的问题时，必须**优先搜索 BuildAdmin 官方资源**，禁止凭猜测给出答案。

### 0.1 官方资源检索顺序

**Step 1：官方文档**（优先级最高）
- 地址：https://doc.buildadmin.com/
- 适用：框架配置、API 用法、组件属性、部署说明等标准问题
- 搜索关键词示例：`baTable column 配置`、`Backend 控制器`、`权限菜单`

**Step 2：官方社区问答**（次优先）
- 地址：https://ask.buildadmin.com/
- 适用：具体报错、非标准用法、版本兼容性、二次开发经验
- 搜索关键词示例：具体的报错信息、功能模块名称

**Step 3：框架源码**（深度问题）
- 路径：`app/common/controller/Backend.php`、`app/admin/library/traits/Backend.php`
- 适用：父类方法签名查阅、Trait 方法实现细节

### 0.2 搜索行为规范

```
遇到棘手问题
      ↓
① 先读 SKILL.md 相关章节（本文件）
      ↓
② 搜索 https://doc.buildadmin.com/  查阅官方文档
      ↓
③ 搜索 https://ask.buildadmin.com/ 查阅社区问答
      ↓
④ 基于检索结果，给出有依据的解答
      ↓
⑤ 若检索无果，明确说明「当前信息不足」，不得凭猜测给出方案
```

**⚠️ 禁止行为**：
- 禁止在未查阅文档的情况下，对框架行为做主观推断
- 禁止将「可能是」「大概是」等不确定表述作为解决方案输出
- 禁止在社区有明确解答的情况下，给出与社区答案相悖的建议

---

## 一、后端开发规范 (PHP 8 + ThinkPHP 6)

### 1.1 【最高优先级】方法签名铁律（避免 Fatal Error 500）

**背景**：BuildAdmin 后台控制器均继承自 `app\common\controller\Backend`，核心 CRUD 方法通过 `app\admin\library\traits\Backend` Trait 混入。PHP 8 中，子类 override 父类或 Trait 的方法时，**签名必须完全兼容**，否则触发 `E_COMPILE_ERROR`（编译期错误），导致整个系统 500 崩溃，且与跨域无关。

**规范**：

```php
// ✅ 必须：重写以下核心方法时，显式声明 `: void` 返回值类型
public function initialize(): void { parent::initialize(); }
public function index(): void     { parent::index(); }
public function add(): void       { parent::add(); }
public function edit(): void      { parent::edit(); }
public function del(): void       { parent::del(); }

// ❌ 禁止：省略 `: void` 会触发 PHP 8 签名不兼容 Fatal Error
public function index() { }
```

**⚠️ 规律**：不确定某方法的签名时，必须先查阅父类或 Trait 源码，再进行重写。绝不允许凭经验猜测。

---

### 1.2 属性声明禁令

PHP 8 对属性类型进行严格校验，在子类顶部重复声明父类已有的带类型属性会直接 Fatal Error。

```php
// ❌ 错误：父类已声明此属性，子类重复声明会导致编译错误
class ArticleController extends Backend
{
    protected $model = null;                    // 父类已有
    protected array $preExcludeFields = [];     // 父类已有（有类型声明）
}

// ✅ 正确：在 initialize() 中动态赋值，不在类顶部声明
class ArticleController extends Backend
{
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new Article();
        $this->preExcludeFields = ['create_time', 'update_time'];
    }
}
```

---

### 1.3 HttpResponseException 捕获陷阱

`$this->success()` 和 `$this->error()` 底层通过抛出 `think\exception\HttpResponseException` 实现响应输出。在 `try-catch` 中捕获 `\Exception` 或 `\Throwable` 时，如果不将其重新抛出，响应会被"吞掉"，导致接口无任何输出或行为异常。

```php
try {
    // 业务逻辑
    $this->success('操作成功');

} catch (\think\exception\HttpResponseException $e) {
    throw $e;  // ✅ 必须重新抛出，交还给框架渲染响应

} catch (\Throwable $e) {
    $this->error('操作失败：' . $e->getMessage());
}
```

---

### 1.4 数据库事务安全

多表写操作必须使用 `Db::transaction()`。若在闭包内使用 `try-catch`，捕获异常后必须再次 `throw`，否则事务无法回滚，造成数据不一致。

```php
Db::transaction(function () {
    try {
        // 写入操作 A：扣减库存
        // 写入操作 B：创建订单
    } catch (\Throwable $e) {
        Log::error('事务失败：' . $e->getMessage());
        throw $e; // ✅ 必须再次抛出，以触发数据库 Rollback
    }
});
```

---

### 1.5 ORM 操作规范

#### 1.5.1 关联查询：优先 with()，避免 JOIN Collation 冲突

```php
// ✅ 推荐：with() 预载入，不涉及数据库 JOIN
$list = ArticleModel::with(['category', 'author'])->select();

// ⚠️ 必须 JOIN 时：所有关联字段的字符集排序规则（Collation）必须统一
// 推荐统一为 utf8mb4_unicode_ci，否则触发 SQLSTATE[1267] 冲突
```

#### 1.5.2 多表 JOIN 字段歧义

当两表存在同名字段（常见如 `id`、`status`、`name`、`create_time`），在排序、搜索、字段选择中必须显式带表名前缀：

```php
// ✅ 正确：消除字段歧义
->field('article.id, article.status, category.name as category_name')
->order('article.create_time', 'desc')

// ❌ 错误：触发 "Column 'status' in field list is ambiguous"
->order('status', 'desc')
```

#### 1.5.3 Model 对象转数组

从 Model 获取数据后进行逻辑处理，必须先调用 `toArray()`：

```php
// ✅ 正确
$item = ArticleModel::find($id);
if ($item) {
    $row = $item->toArray();
    echo $row['title'];
}

// ❌ 错误：ThinkPHP Model 对象不能作为数组使用
$item = ArticleModel::find($id);
echo $item['title']; // 报错：Cannot use object of type Model as array
```

#### 1.5.4 原生 Db 查询保留字字段

使用 `Db::name()` 进行查询时，MySQL 保留字字段名（如 `order`、`key`、`status`、`rank`）必须用反引号包裹：

```php
// ✅ 正确：保留字字段加反引号
Db::name('article')
    ->field('`order`, `status`, sum(view_count) as total_views')
    ->group('`status`')
    ->select()
    ->toArray(); // 必须 toArray()，否则 array_column 等 PHP 数组函数报错
```

---

### 1.6 响应规范

禁止使用 `echo`、`json()`、`return`、`header()` 等原生 PHP 响应方式，必须使用框架提供的方法：

```php
// ✅ 正确
$this->success('操作成功', ['id' => $id]);
$this->error('参数错误');

// ❌ 禁止
echo json_encode(['code' => 1]);
return json(['code' => 1]);
```

---

### 1.7 数据验证规范

禁止在 Controller 中写大量 `if` 校验逻辑，必须使用 `app/admin/validate/` 目录下的验证器类：

```php
// ✅ 正确：使用验证器
$this->svalidate($data, 'Article', 'add');

// ❌ 禁止：在 Controller 中堆砌 if 校验
if (empty($data['title'])) {
    $this->error('标题不能为空');
}
if (strlen($data['title']) > 100) {
    $this->error('标题不能超过100字');
}
```

---

### 1.8 敏感字段过滤

敏感字段（如密码 Hash、成本价、内部备注等）必须在 Controller 中配置 `$preExcludeFields` 进行过滤：

```php
public function initialize(): void
{
    parent::initialize();
    $this->model = new Product();
    // 对外 API 不暴露成本价和利润字段
    $this->preExcludeFields = ['cost_price', 'profit_margin', 'supplier_note'];
}
```

---

### 1.9 500 错误排查 SOP（必须按顺序执行）

**铁律**：出现 500 或 CORS 报错，**优先排查后端 PHP Fatal Error，禁止优先修改前端配置**。

```
遇到 500 / CORS 报错
        ↓
Step 1: php -l app/admin/controller/YourController.php   ← 语法合法性检查
        ↓
Step 2: 查阅 runtime/log/ 最新日志（搜索 Fatal error / Type error / Compile error）
        ↓
Step 3: 检查所有 override 方法的签名是否包含 `: void`
        ↓
Step 4: 检查属性声明是否与父类冲突（是否在类顶部重复声明了带类型的属性）
        ↓
Step 5: 检查 try-catch 是否错误地吞掉了 HttpResponseException
        ↓
Step 6: 定位具体的业务逻辑 Bug
```

---

## 二、数据库设计规范（驱动 CRUD 自动生成）

BuildAdmin 的 CRUD 代码生成器通过**字段类型 + 字段名后缀 + 字段注释**三要素，自动映射前端 UI 组件。设计数据表时必须遵守以下规范。

### 2.1 字段类型自动映射

| MySQL 字段类型 | 自动生成的前端 UI 组件 |
| :--- | :--- |
| `enum` | Radio 单选框 |
| `set` | Checkbox 多选框 |
| `date` | 日期选择器 |
| `datetime` / `timestamp` | 时间日期选择器 |
| `decimal` / `float` / `double` | 数字输入框（含小数步长） |
| `int` / `tinyint` / `bigint` | 整型数字输入框（步长为 1） |
| `text` / `longtext` / `mediumtext` | 多行文本域（Textarea） |

### 2.2 字段名后缀硬性约定（核心）

> **只要字段名以下列字符串结尾**，CRUD 生成器将自动触发对应的高级 UI 组件，无论字段本身是什么类型。

#### 关联与选择类
| 后缀 | 触发 UI 组件 |
| :--- | :--- |
| `_id` | 远程 Select（单选，自动关联对应主表） |
| `_ids` | 远程 Select（多选） |
| `list` / `select` / `data` | 下拉框（单选） |
| `lists` / `selects` / `multi` | 下拉框（多选） |
| `city` | 省市县三级城市选择器 |
| `icon` | 图标选择器 |
| `color` | 颜色拾取器 |

#### 状态与开关类
| 后缀 | 触发 UI 组件 | 说明 |
| :--- | :--- | :--- |
| `status` / `state` / `type` | Radio 单选框 | 配合注释字典使用 |
| `switch` / `toggle` | Switch 快捷开关 | `1` = 开，`0` = 关 |

#### 媒体与文件类
| 后缀 | 触发 UI 组件 |
| :--- | :--- |
| `image` / `avatar` | 单图上传组件 |
| `images` / `avatars` | 多图上传组件 |
| `file` | 单文件上传组件 |
| `files` | 多文件上传组件 |

#### 内容类
| 后缀 | 触发 UI 组件 |
| :--- | :--- |
| `content` / `editor` | 富文本编辑器（配合 `text` 类型字段） |
| `array` | 数组输入组件 |
| `textarea` / `multiline` | 强制多行文本域（即使是 varchar） |

### 2.3 字段注释字典语法（枚举/状态字段必用）

字段注释不仅是说明，更是 CRUD 引擎解析前端枚举选项的数据源。

**语法格式**：`[说明文本]:[数据库值]=[展示文案],[数据库值]=[展示文案]`

```sql
-- ✅ 正确示例
`status`     tinyint(1)  COMMENT '状态:0=禁用,1=启用'
`type`       tinyint(1)  COMMENT '文章类型:1=普通文章,2=置顶文章,3=精华文章'
`pay_status` tinyint(1)  COMMENT '支付状态:0=待支付,1=已支付,2=已退款'
`gender`     tinyint(1)  COMMENT '性别:0=未知,1=男,2=女'
```

### 2.4 数据表设计红线

1. **禁止复合主键**：每张表有且仅有一个自增整型单字段主键 `id`
2. **系统保留字段**（底层自动维护，禁止手动写入）：
   - `weigh` (`int`)：权重排序，自动生成拖拽排序功能
   - `create_time` (`bigint`)：记录创建时间戳
   - `update_time` (`bigint`)：记录更新时间戳
3. **表注释规范**：表注释若以"表"字结尾（如 `文章信息表`），CRUD 生成器自动渲染页面标题为 `文章信息管理`

---

## 三、前端开发规范 (Vue 3 + TypeScript)

### 3.1 baTable 强制使用规范

后台 CRUD 页面**必须**基于 `baTable` 库开发，禁止手写底层 `<el-table>` 或 `<el-form-item>` 代码。

```typescript
// ✅ 正确：通过 baTable column 配置驱动页面
baTable.mount({
    pk: 'id',
    column: [
        { label: '标题', prop: 'title', operator: 'LIKE' },
        { label: '状态', prop: 'status', render: 'tag',
          replaceValue: { '0': '禁用', '1': '启用' } },
        { label: '封面', prop: 'cover_image', render: 'image' },
        { label: '创建时间', prop: 'create_time', render: 'datetime', operator: 'RANGE' },
    ]
})

// ❌ 禁止：手写大量 HTML 表格代码
<el-table-column prop="status" label="状态">
    <template #default="scope">
        <el-tag>{{ scope.row.status === 1 ? '启用' : '禁用' }}</el-tag>
    </template>
</el-table-column>
```

### 3.2 渲染安全防御：强制可选链

在 Vue 模板中渲染后端动态数据时，任何属性访问**必须**使用 `?.` 可选链运算符，防止因数据未加载或为 `null`/`undefined` 时导致前端白屏崩溃：

```html
<!-- ✅ 正确：使用可选链防御 -->
<span>{{ scope.row?.category?.name }}</span>
<span :class="scope.row?.status === 1 ? 'success' : 'danger'">
    {{ scope.row?.status_text }}
</span>

<!-- ❌ 禁止：任何一层为 undefined 都会导致 TypeError 白屏 -->
<span>{{ scope.row.category.name }}</span>
```

### 3.3 按钮级权限控制 (v-auth)

后台操作按钮（如导出、审核、重置密码等特殊操作）必须配合 `v-auth` 指令进行权限校验。

```html
<!-- 前端：v-auth 的标识字符串必须与后端控制器方法名保持一致 -->
<el-button v-auth="'export'" @click="handleExport">导出</el-button>
<el-button v-auth="'review'" @click="handleReview">审核</el-button>
```

```php
// 后端控制器：必须有同名方法，框架才能自动识别权限节点
public function export(): void { ... }
public function review(): void { ... }
```

### 3.4 TypeScript 类型安全（禁止 any）

所有 API 请求参数与响应数据必须定义明确的 TypeScript `interface`，禁止使用 `any` 类型：

```typescript
// ✅ 正确
interface ArticleItem {
    id: number
    title: string
    status: 0 | 1
    category_id: number
    create_time: number
}

interface ArticleListResponse {
    list: ArticleItem[]
    total: number
    remark: string
}

// ❌ 禁止
const res: any = await getArticleList(params)
```

### 3.5 组件语法强制规范

所有 Vue 组件必须使用 `<script setup lang="ts">` 语法，禁止使用 Options API 或非 TypeScript 模式：

```vue
<!-- ✅ 正确 -->
<script setup lang="ts">
import { ref, reactive } from 'vue'
const loading = ref(false)
</script>

<!-- ❌ 禁止 -->
<script>
export default {
    data() {
        return { loading: false }
    }
}
</script>
```

### 3.6 状态管理规范

禁止使用全局变量或 Window 对象传递状态，必须使用 Vue 响应式 API 或 Pinia Store：

```typescript
// ✅ 正确
const loading = ref(false)
const formData = reactive({ title: '', status: 1 })

// ❌ 禁止
window.globalLoading = false
(window as any).formCache = formData
```

---

## 四、API 接口封装规范

### 4.1 接口文件位置

| 接口类型 | 存放位置 |
| :--- | :--- |
| 后台 Admin 接口 | `web/src/api/backend/` |
| 前台用户接口 | `web/src/api/frontend/` 或 `web-nuxt/api/` |

### 4.2 禁止直接调用原生 Axios

必须使用项目封装的请求工具函数（通常为 `Http.get()` / `Http.post()` 或 `request()`），禁止直接调用 `axios.get()` / `axios.post()`：

```typescript
// ✅ 正确：使用项目封装的请求方法
export function getArticleList(params: ArticleQueryParams) {
    return Http.get({ url: '/admin/article', params })
}

export function createArticle(data: ArticleCreateForm) {
    return Http.post({ url: '/admin/article/add', data })
}

// ❌ 禁止：直接调用原生 Axios
import axios from 'axios'
axios.get('/admin/article')
```

---

## 五、常见高危场景快速决策树

```
新建后台 Controller？
  → 继承 Backend，检查所有方法签名含 : void
  → initialize() 中赋值 model 和 preExcludeFields
  → php -l 验证语法

需要多表写操作？
  → 包裹在 Db::transaction() 中
  → 闭包内 catch 到异常后必须再次 throw

新建数据表？
  → 单字段自增主键 id（禁止复合主键）
  → status/type 字段写注释字典（如 '状态:0=禁用,1=启用'）
  → 图片字段用 image/images 后缀，文件用 file/files 后缀
  → 关联外键用 _id / _ids 后缀

新建前端 CRUD 页面？
  → 必须用 baTable.mount() column 配置
  → 模板中所有动态数据访问加 ?. 可选链
  → 接口定义加 TypeScript interface，禁止 any
  → 使用 <script setup lang="ts">

遇到 500 报错？
  → 不要改前端，不要改 CORS
  → php -l 检查语法 → 看 runtime/log 日志 → 查方法签名
```

---

## 六、代码生成前自查清单

提交代码前，逐项确认：

- [ ] 控制器是否继承了正确的基类（`Backend` 或 `Frontend`）？
- [ ] 所有 override 方法是否声明了 `: void` 返回值类型？
- [ ] 是否在子类顶部重复声明了父类属性（`$model`、`$preExcludeFields`）？
- [ ] `try-catch` 中是否有重新抛出 `HttpResponseException`？
- [ ] 多表写操作是否都包裹在 `Db::transaction()` 中，且 catch 后再次 `throw`？
- [ ] 前台页面是否基于 `baTable.mount()` 的 `column` 配置，而非手写表格？
- [ ] Vue 模板中的所有动态数据是否都使用了 `?.` 可选链？
- [ ] 是否有 `any` 类型需要替换为具体的 `interface`？
- [ ] 组件是否使用了 `<script setup lang="ts">`？
- [ ] 接口请求是否使用了项目封装的请求方法（非原生 Axios）？
- [ ] 敏感字段是否在 `$preExcludeFields` 中配置了过滤？
- [ ] 枚举/状态字段的注释是否符合字典语法（`状态:0=禁用,1=启用`）？
- [ ] 自定义 UI 页面是否已为暗色模式提供对应的 `.dark` 样式覆盖？
- [ ] 加载状态是否有骨架屏或 Spinner 占位，而非空白区域？
- [ ] 数字数据是否使用了与单位匹配的格式化函数？
- [ ] 响应式布局是否覆盖了移动端断点（`max-width: 640px`）？
- [ ] 遇到棘手问题是否已先查阅 https://doc.buildadmin.com/ 和 https://ask.buildadmin.com/ ？

---

## 七、前端 UI/UX 交互规范

以下规范来自 BuildAdmin 二次开发的实战沉淀，覆盖自定义展示页面（非 baTable CRUD 页面）中高频出现的交互问题。

### 7.1 双主题适配规范（亮色/暗色模式）

BuildAdmin 内置暗色模式切换，**所有自定义 CSS 必须同时为亮色和暗色模式编写样式**，否则页面在暗色模式下会出现白色背景、文字不可见等严重视觉问题。

**规范**：
- 基础样式为亮色，通过 `.dark` 父选择器覆盖暗色模式样式
- 优先使用 CSS 变量（`var(--el-color-primary)` 等 Element Plus 变量）以自动适配
- 颜色使用 `rgba()` 半透明值而非纯色，以便在两种模式下都保持视觉层次感

```scss
// ✅ 正确：同时为亮/暗模式编写
.my-card {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(226, 232, 240, 0.8);
    color: #1e293b;
}

.dark {
    .my-card {
        background: rgba(18, 20, 38, 0.7);
        border-color: rgba(99, 102, 241, 0.16);
        color: #f1f5f9;
    }
}

// ❌ 禁止：只写亮色样式，暗色模式下显示异常
.my-card {
    background: #ffffff;
    color: #000000;
}
```

**常用暗色模式颜色参考**：
| 用途 | 亮色值 | 暗色值 |
| :--- | :--- | :--- |
| 卡片背景 | `rgba(255,255,255,0.9)` | `rgba(18,20,38,0.7)` |
| 边框 | `rgba(226,232,240,0.8)` | `rgba(99,102,241,0.16)` |
| 主文字 | `#1e293b` | `#f1f5f9` |
| 次要文字 | `#64748b` | `#94a3b8` |
| 分割线 | `#f1f5f9` | `#1e293b` |
| 悬停背景 | `#f8fafc` | `rgba(255,255,255,0.02)` |

---

### 7.2 加载状态规范（禁止空白等待）

所有异步数据加载期间，页面不得出现空白区域。必须使用加载状态占位，提供明确的视觉反馈。

```html
<!-- ✅ 正确：三态渲染（加载中 → 权限拦截 → 正常数据） -->
<div v-if="loading" class="loader-box">
    <!-- 加载 Spinner 或骨架屏 -->
    <div class="spinner"></div>
    <p>正在加载数据...</p>
</div>

<div v-else-if="hasError" class="error-card">
    <!-- 错误或权限不足提示 -->
</div>

<div v-else class="data-content">
    <!-- 正常数据展示 -->
</div>

<!-- ❌ 禁止：没有加载状态，数据未到时一片空白 -->
<div>{{ data.title }}</div>
```

**加载状态设计原则**：
- 加载容器设置 `min-height`（建议 `300px`），防止页面高度抖动
- 加载文案需有语境（如「正在加载订单数据...」），而非通用的 `Loading...`
- 图表/大数据集加载时，优先使用骨架屏（Skeleton）而非 Spinner

---

### 7.3 数字格式化规范

根据数值的量级和上下文，选择合适的格式化方式，禁止将原始数字直接渲染到页面：

```typescript
// ✅ 金额格式化（美元）
const formatUSD = (val: number | null | undefined): string => {
    if (val === undefined || val === null) return '$0'
    return '$' + Math.round(Number(val)).toLocaleString()
}
// 输出示例：$1,234,567

// ✅ 紧凑数字格式化（大数字缩写，用于 Hero 统计面板）
const formatCompact = (val: number): string => {
    if (val >= 1e9) return (val / 1e9).toFixed(2) + ' B'
    if (val >= 1e6) return (val / 1e6).toFixed(2) + ' M'
    if (val >= 1e4) return (val / 1e4).toFixed(2) + ' 万'
    return Math.round(val).toLocaleString()
}
// 输出示例：12.34 亿 | 3.56 M

// ✅ 百分比格式化（含正负号）
const formatPercent = (val: number | null | undefined): string => {
    if (val === undefined || val === null) return '-'
    const num = Number(val)
    return (num >= 0 ? '+' : '') + num.toFixed(2) + '%'
}
// 输出示例：+12.34% | -3.21%

// ❌ 禁止：直接渲染原始数字
<span>{{ item.amount }}</span>  <!-- 输出：1234567，无格式、无单位 -->
```

---

### 7.4 涨跌趋势标签规范（增长率可视化）

展示同比/环比、增长率等数据时，必须配合颜色和图标进行语义化区分，不得仅显示纯数字：

```typescript
// ✅ 涨跌样式计算函数
const getTrendClass = (val: number | null | undefined): string => {
    if (val === null || val === undefined) return 'trend--flat'
    if (val > 0.05) return 'trend--up'    // 显著上涨
    if (val < -0.05) return 'trend--down'  // 显著下跌
    return 'trend--flat'                   // 持平（-0.05 到 +0.05）
}
```

```scss
// ✅ 涨跌标签对应 CSS（必须同时提供暗色模式覆盖）
.trend--up   { background: rgba(16,185,129,0.08); color: #10b981; }  // 绿色：上涨
.trend--down { background: rgba(244,63,94,0.08);  color: #f43f5e; }  // 红色：下跌
.trend--flat { background: rgba(100,116,139,0.08); color: #64748b; } // 灰色：持平

.dark {
    .trend--up   { background: rgba(16,185,129,0.15); color: #34d399; }
    .trend--down { background: rgba(244,63,94,0.15);  color: #fb7185; }
    .trend--flat { background: rgba(148,163,184,0.12); color: #94a3b8; }
}
```

---

### 7.5 进度条/占比可视化规范

展示占比数据时，文字百分比必须配合视觉进度条，增强数据直观性：

```html
<!-- ✅ 正确：文字 + 进度条组合 -->
<div class="ratio-cell">
    <div class="ratio-bar-bg">
        <div class="ratio-bar" :style="{ width: item.ratio + '%' }"></div>
    </div>
    <span class="ratio-text">{{ item.ratio }}%</span>
</div>
```

```scss
// 进度条样式（含过渡动画和暗色模式）
.ratio-bar-bg {
    flex-grow: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 99px;
    overflow: hidden;
}
.ratio-bar {
    height: 100%;
    background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
    border-radius: 99px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); // ✅ 必须：入场动画
}
.dark .ratio-bar-bg { background: #334155; }
.dark .ratio-bar    { background: linear-gradient(90deg, #818cf8 0%, #60a5fa 100%); }
```

---

### 7.6 响应式布局断点规范

**所有自定义页面必须在以下断点处验证**，不得只考虑桌面端：

| 断点 | 宽度 | 常见处理 |
| :--- | :--- | :--- |
| 移动端 | `max-width: 640px` | 取消 padding、表格横向滚动、隐藏次要列 |
| 平板端 | `max-width: 768px` | 单列布局、Hero 面板垂直排列 |
| 桌面端 | `min-width: 1024px` | 多列网格、侧边栏展开 |

```scss
// ✅ 正确：移动端针对性覆盖
.data-card { padding: 32px; }

@media (max-width: 640px) {
    .data-card { padding: 16px; }      // 压缩内边距
    .data-table th,
    .data-table td { padding: 10px; }  // 压缩单元格
}

// ✅ 表格在移动端允许横向滚动（禁止内容溢出或强制换行）
.table-wrapper { overflow-x: auto; }
```

---

### 7.7 排行榜/前三名高光规范

排行榜类页面，前三名必须通过视觉差异化（徽章/颜色/图标）与后续名次区分：

```scss
// ✅ Top3 差异化徽章
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px; height: 28px;
    border-radius: 50%;

    // 第一名：金色
    &--1 { background: linear-gradient(135deg, #fef3c7, #f59e0b); border: 2px solid #fbbf24; &::after { content: '🥇'; } }
    // 第二名：银色
    &--2 { background: linear-gradient(135deg, #f1f5f9, #cbd5e1); border: 2px solid #94a3b8; &::after { content: '🥈'; } }
    // 第三名：铜色
    &--3 { background: linear-gradient(135deg, #fff7ed, #fed7aa); border: 2px solid #f97316; &::after { content: '🥉'; } }
}
```

---

### 7.8 CSV/Excel 导出规范

提供数据导出功能时，必须处理编码和字段格式问题，以确保 Excel 正确打开中文内容：

```typescript
// ✅ 正确：带 BOM 头的 UTF-8 CSV 导出（防止 Excel 打开中文乱码）
const exportToCSV = (data: any[], filename: string) => {
    let content = '\uFEFF' // BOM 头（必须）
    content += '字段1,字段2,字段3\n'

    data.forEach(item => {
        // 过滤字段中可能含有的英文逗号（防止 CSV 列错位）
        const safeName = String(item.name).replace(/,/g, ' ')
        content += `${safeName},${item.value},${item.ratio}%\n`
    })

    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `${filename}.csv`
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url) // ✅ 必须：释放内存
}

// ❌ 禁止：不带 BOM 头，中文在 Excel 中乱码
const blob = new Blob([content], { type: 'text/csv' })
```

---

### 7.9 Hero Banner / 页面头部设计规范

数据展示类页面的 Hero 区域，遵循以下设计规范：

```scss
// ✅ Hero 区域标准结构规范
.page-hero {
    // 深色渐变背景（与页面整体风格协调）
    background: linear-gradient(135deg, #0b0c24 0%, #141539 55%, #0f1a3d 100%);
    border-bottom: 1px solid rgba(99, 102, 241, 0.2);
    overflow: hidden; // 防止装饰元素溢出
}

.page-hero__inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 56px 32px 52px; // 上下留白充足

    @media (max-width: 768px) {
        padding: 40px 20px 36px; // 移动端收窄
    }
}

// 标题使用响应式字号（clamp 防止小屏溢出）
.page-hero__title {
    font-size: clamp(28px, 4vw, 46px); // ✅ 必须：最小值/推荐值/最大值
    font-weight: 900;
    line-height: 1.2;
}

// 渐变文字高亮（跨浏览器兼容写法）
.page-hero__accent {
    background: linear-gradient(90deg, #818cf8 0%, #38bdf8 50%, #34d399 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
```

**Hero 区域布局要点**：
- 使用 `flex-wrap: wrap` 配合 `min-width` 保证在平板端两列自然折叠为单列
- 右侧统计面板 `flex-shrink: 0` 防止被左侧文字内容压缩
- 面包屑导航颜色使用 `rgba(255,255,255,0.4)` 低调显示，悬停后高亮

---

### 7.10 按钮交互微动效规范

所有可交互按钮，必须有明确的 hover 和 active 状态反馈：

```scss
// ✅ 标准按钮微动效
.btn {
    transition: all 0.25s ease;

    &:hover {
        transform: translateY(-2px);        // 上浮 2px
        box-shadow: 0 8px 20px rgba(0,0,0,0.12); // 投影加深
    }
    &:active {
        transform: translateY(0);           // 按下回位
        box-shadow: none;
    }
}

// ✅ 导出/操作类按钮（颜色变化 + 轻微上浮）
.btn-action {
    &:hover {
        border-color: var(--el-color-primary);
        color: var(--el-color-primary);
        transform: translateY(-1px); // 操作按钮上浮幅度小于主按钮
    }
    &:active { transform: translateY(1px); } // 按下时向下
}

// ❌ 禁止：无任何交互反馈的按钮（用户无法判断是否可点击）
.btn { cursor: pointer; } // 仅改变鼠标样式，无视觉反馈
```

---

### 7.11 Tab 切换组规范（分段选择器）

功能筛选/视图切换使用胶囊形分段选择器，而非普通 Radio 按钮：

```html
<!-- ✅ 正确：胶囊型 Tab 分组 -->
<div class="tab-group">
    <button
        v-for="opt in options"
        :key="opt.value"
        :class="['tab-btn', activeTab === opt.value ? 'tab-btn--active' : '']"
        @click="activeTab = opt.value"
    >
        {{ opt.label }}
    </button>
</div>
```

```scss
.tab-group {
    display: inline-flex;
    background: #f1f5f9;   // 容器背景
    padding: 4px;
    border-radius: 12px;
}
.tab-btn {
    padding: 6px 16px;
    border-radius: 9px;    // 比容器小 3px，形成内嵌感
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    transition: all 0.2s;
    border: none;
    background: transparent;
    cursor: pointer;

    &:hover { color: #0f172a; }

    &--active {
        background: #ffffff;
        color: #6366f1;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.12);
    }
}
// 暗色模式
.dark {
    .tab-group { background: #1e293b; }
    .tab-btn--active { background: #0f172a; color: #818cf8; }
}
```
