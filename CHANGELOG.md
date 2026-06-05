# Changelog

All notable changes to `buildadmin-dev-skill` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] - 2026-06-05

### Added

#### 后端规范（PHP 8 + ThinkPHP 6）
- PHP 8 方法签名兼容性规范（`initialize/index/add/edit/del` 均须显式声明 `: void`）
- 子类顶部属性声明禁令（禁止重复声明父类带类型属性）
- `HttpResponseException` 捕获陷阱说明与规避写法
- 数据库事务安全规范（`Db::transaction()` + catch 后必须再次 `throw`）
- ORM 操作规范（`with()` 优先、`toArray()` 强制、保留字字段加反引号）
- 多表 JOIN Collation 冲突规避指南
- 响应规范（禁止 `echo`/`json()`，必须用 `$this->success()` / `$this->error()`）
- 数据验证规范（禁止 Controller 内堆砌 `if` 校验，必须使用 Validate 类）
- 敏感字段过滤规范（`$preExcludeFields` 配置）
- 500 错误六步排查 SOP

#### 数据库设计规范
- 字段类型 → UI 组件自动映射完整对照表（7 种 MySQL 类型）
- 字段名后缀硬性约定（20+ 后缀规则，涵盖关联/状态/媒体/内容类）
- 字段注释字典语法（`状态:0=禁用,1=启用`）
- 数据表设计红线（单字段主键、系统保留字段）

#### 前端规范（Vue 3 + TypeScript）
- baTable 强制使用规范（禁止手写 `<el-table>`）
- 渲染安全防御（`?.` 可选链强制要求）
- `v-auth` 按钮权限控制规范（与后端方法名保持一致）
- TypeScript 类型安全规范（禁止 `any`，必须定义 `interface`）
- `<script setup lang="ts">` 强制语法规范
- 状态管理规范（禁止 `window` 全局变量）

#### API 接口封装规范
- 接口文件目录规范
- 禁止直接调用原生 Axios 规范

#### 棘手问题搜索策略
- 三级检索顺序：官方文档 → 社区问答 → 框架源码
- 明确禁止行为（禁止未查文档进行主观推断）

#### 前端 UI/UX 交互规范（11 条）
- 双主题适配规范（亮/暗色模式，含颜色参考表）
- 加载状态规范（三态渲染模式）
- 数字格式化规范（金额/紧凑/百分比三套函数）
- 涨跌趋势标签语义化规范（含完整 CSS 模板）
- 进度条/占比可视化规范（含 `cubic-bezier` 入场动画）
- 响应式断点规范（移动 640px / 平板 768px / 桌面 1024px）
- 排行榜前三名高光徽章规范
- CSV/Excel 导出规范（UTF-8 BOM 头、逗号转义、内存释放）
- Hero Banner 设计规范（`clamp()` 响应式字号、渐变文字兼容写法）
- 按钮交互微动效规范（hover/active 状态标准值）
- Tab 切换组规范（胶囊分段选择器完整模板）

#### 配套资源
- `templates/admin-controller.tpl.php`：Admin 后台控制器标准骨架
- `templates/validate.tpl.php`：数据验证器标准骨架
- `templates/batablepage.tpl.vue`：baTable CRUD 页面标准骨架
- `templates/api-request.tpl.ts`：前端 TypeScript 接口定义标准骨架
- `scripts/check-syntax.ps1`：PHP 批量语法检测脚本（Windows PowerShell）
- `references/batable-columns.md`：baTable column 属性完整字典
- `references/common-exceptions.md`：高频报错原因与解决方案速查手册
