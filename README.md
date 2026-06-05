<p align="center">
  <img src="https://img.shields.io/badge/BuildAdmin-二次开发-6366f1?style=for-the-badge&logo=vuedotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/ThinkPHP-6.x-green?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Vue-3.x-42b883?style=for-the-badge&logo=vuedotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" />
</p>

<h1 align="center">buildadmin-dev-skill</h1>

<p align="center">
  <b>BuildAdmin 二次开发 · AI Vibe Coding 必装 SKILL</b><br/>
  专为 AI 编码助手（Cursor / Windsurf / Antigravity 等）设计，让 AI 自动遵守 BuildAdmin 框架规范
</p>

---

## 📖 为什么需要这个 SKILL？

在使用 AI Vibe Coding 工具对 [BuildAdmin](https://github.com/build-admin/buildadmin) 进行二次开发时，AI 模型经常因不了解框架内部规范而生成存在以下问题的代码：

| 问题 | 后果 |
| :--- | :--- |
| PHP 8 方法签名不兼容（省略 `: void`） | 系统 **500 崩溃**，日志报 `E_COMPILE_ERROR` |
| 子类顶部重复声明父类带类型属性 | 编译期 **Fatal Error** |
| 手写 `<el-table>` 代替 baTable | 破坏框架 **CRUD 生成规范** |
| `try-catch` 捕获了 `HttpResponseException` 未重新抛出 | 接口**静默无响应** |
| 接口返回类型使用 `any` | 失去 **TypeScript 类型保护** |
| 自定义 CSS 未覆盖暗色模式 | 暗色下**页面显示异常** |

安装本 SKILL 后，AI 编码助手将在生成或修改代码时**自动遵守**上述规范，从根源减少以上问题的发生。

---

## 📦 目录结构

```text
buildadmin-dev-skill/
├── SKILL.md                         # 主技能指令（AI 读取核心，必须包含）
├── README.md                        # 本文件
├── CHANGELOG.md                     # 版本更新记录
├── templates/                       # 可直接复制使用的代码模板
│   ├── admin-controller.tpl.php     # Admin 后台控制器骨架
│   ├── validate.tpl.php             # 数据验证器骨架
│   ├── batablepage.tpl.vue          # baTable CRUD 页面骨架
│   └── api-request.tpl.ts           # 前端 TypeScript 接口定义骨架
├── scripts/
│   └── check-syntax.ps1             # PHP 批量语法检测脚本（Windows PowerShell）
└── references/                      # AI 可按需读取的参考手册
    ├── batable-columns.md           # baTable column 属性完整字典
    └── common-exceptions.md         # 高频报错原因与解决方案速查
```

---

## 🚀 安装方法

### 方法一：项目内安装（推荐，仅对单个项目生效）

```bash
# 克隆本仓库
git clone https://github.com/your-username/buildadmin-dev-skill.git

# 将整个目录复制到你的 BuildAdmin 项目的 .agent/skills/ 目录下
cp -r buildadmin-dev-skill /your-buildadmin-project/.agent/skills/
```

安装后的目录结构：
```text
your-buildadmin-project/
├── .agent/
│   └── skills/
│       └── buildadmin-dev-skill/    ← 放在这里
│           ├── SKILL.md             ← AI 会自动读取此文件
│           └── ...
├── app/
├── web/
└── ...
```

### 方法二：全局安装（对所有 BuildAdmin 项目生效）

将 `buildadmin-dev-skill/` 目录放置在您使用的 AI 工具的全局 skills 配置目录中。

> 不同 AI 工具的全局 skills 目录路径各有不同，请参考您所使用工具的官方文档。

---

## ✅ SKILL 覆盖范围

### 第零章：棘手问题搜索策略
- 遇到问题时优先检索 BuildAdmin 官方文档 `doc.buildadmin.com` 和社区 `ask.buildadmin.com`
- 明确禁止 AI 在未查阅文档情况下对框架行为进行主观推断

### 第一章：后端开发规范（PHP 8 + ThinkPHP 6）
- ✅ PHP 8 方法签名兼容性（避免 Fatal Error）
- ✅ 子类属性声明禁令
- ✅ `HttpResponseException` 捕获陷阱规避
- ✅ 数据库事务安全（Rollback 保证）
- ✅ ORM 操作规范（`toArray()`、关联查询）
- ✅ 多表 JOIN 字段歧义消除
- ✅ 响应规范（禁止 `echo`/`json()`）
- ✅ 数据验证规范（Validate 类强制使用）
- ✅ 敏感字段过滤
- ✅ **500 错误六步排查 SOP**

### 第二章：数据库设计规范（驱动 CRUD 自动生成）
- ✅ 字段类型 → UI 组件自动映射（完整对照表）
- ✅ 字段名后缀硬性约定（`_id`/`status`/`switch`/`image` 等 20+ 后缀）
- ✅ 字段注释字典语法（`状态:0=禁用,1=启用`）
- ✅ 数据表设计红线（单字段主键、系统保留字段）

### 第三章：前端开发规范（Vue 3 + TypeScript）
- ✅ `baTable.mount()` column 配置强制规范
- ✅ 渲染安全防御（`?.` 可选链）
- ✅ `v-auth` 按钮权限控制
- ✅ TypeScript 类型安全（禁止 `any`）
- ✅ `<script setup lang="ts">` 强制规范
- ✅ 状态管理规范

### 第四章：API 接口封装规范
- ✅ 接口文件目录规范
- ✅ 禁止直接调用原生 Axios

### 第五章：高危场景快速决策树

### 第六章：代码生成前自查清单（18 条）

### 第七章：前端 UI/UX 交互规范（11 条）
- ✅ 双主题适配（亮色/暗色，含颜色参考表）
- ✅ 加载状态规范（禁止空白等待）
- ✅ 数字格式化规范（金额/紧凑/百分比三套函数）
- ✅ 涨跌趋势标签语义化（含 CSS 模板）
- ✅ 进度条/占比可视化（含入场动画）
- ✅ 响应式断点规范（移动/平板/桌面）
- ✅ 排行榜前三名高光徽章
- ✅ CSV/Excel 导出（BOM 头处理、逗号转义）
- ✅ Hero Banner 设计规范（`clamp()` 响应式字号）
- ✅ 按钮交互微动效（hover/active 状态）
- ✅ Tab 切换组（胶囊分段选择器）

---

## 🔧 内置工具

### PHP 语法批量检测脚本

```powershell
# 检测单个控制器文件
.\scripts\check-syntax.ps1 -Path "app/admin/controller/Article.php"

# 检测整个控制器目录（递归）
.\scripts\check-syntax.ps1 -Path "app/admin/controller"
```

---

## 📚 参考手册

- 📋 [baTable Column 属性完整字典](./references/batable-columns.md) — `render`、`operator`、`buttons` 等所有配置项说明
- 🐛 [高频报错速查手册](./references/common-exceptions.md) — 常见 Fatal Error、SQL 报错、前端白屏原因与解决方案

---

## 🛠️ 适用版本

| 组件 | 测试版本 |
| :--- | :--- |
| BuildAdmin | >= 3.x |
| PHP | 8.0 / 8.1 / 8.2 |
| ThinkPHP | 6.x |
| Vue | 3.x |
| TypeScript | 4.x / 5.x |

---

## 🤝 贡献指南

欢迎通过 Issue 或 PR 补充更多 BuildAdmin 二次开发中的高频陷阱与最佳实践！

提交 PR 时，请遵循以下格式：

```markdown
## 新增规范：[规范名称]

**问题背景**：描述该规范解决了什么问题

**错误示例**：
\```php / typescript / vue
// 会引发问题的代码
\```

**正确示例**：
\```php / typescript / vue
// 符合规范的代码
\```

**影响范围**：该问题在哪些场景下会出现
```

---

## 📄 License

[MIT License](./LICENSE)

---

## 🔗 相关链接

- 🏠 [BuildAdmin 官网](https://www.buildadmin.com/)
- 📖 [BuildAdmin 官方文档](https://doc.buildadmin.com/)
- 💬 [BuildAdmin 社区问答](https://ask.buildadmin.com/)
- 🐙 [BuildAdmin GitHub](https://github.com/build-admin/BuildAdmin)
