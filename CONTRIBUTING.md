# 贡献指南

感谢你对 `buildadmin-dev-skill` 的关注！本项目欢迎来自 BuildAdmin 开发者社区的任何贡献。

---

## 🐛 报告问题（Issue）

在提交 Issue 之前，请先：

1. 搜索 [已有 Issues](../../issues) 确认没有重复
2. 能提供最小复现示例更佳

**Issue 分类**：
- `bug`：SKILL 中的规范描述有误，或示例代码本身存在问题
- `enhancement`：希望补充新的开发规范
- `question`：对某条规范有疑问

---

## 🔀 提交 PR

### 适合提交 PR 的内容

- 补充新的 BuildAdmin 开发陷阱与规避规范
- 修正现有规范中的错误或不准确表述
- 完善代码模板（`templates/` 目录）
- 新增报错速查条目（`references/common-exceptions.md`）
- 扩展 baTable 属性字典（`references/batable-columns.md`）

### PR 格式要求

补充新规范时，请在 `SKILL.md` 或对应的 references 文件中，按以下格式编写：

````markdown
### X.X 规范标题

**问题背景**：
描述该规范解决了什么问题，以及在什么情况下会触发。

**错误示例**：
```php (or typescript / vue / scss)
// ❌ 会引发问题的代码
// 附上会导致的错误信息或现象
```

**正确示例**：
```php (or typescript / vue / scss)
// ✅ 符合规范的代码
```

**影响范围**：
该问题在哪些 BuildAdmin 版本、哪些使用场景下会出现。
````

### 流程

```
Fork 仓库
    ↓
创建功能分支（feature/add-xxx-rule）
    ↓
编写规范内容
    ↓
在 CHANGELOG.md 中补充变更记录
    ↓
提交 PR，填写 PR 描述
```

---

## 📌 注意事项

- **不要包含任何项目业务代码**：示例代码必须使用通用、泛化的字段名和业务名称（如 `Article`、`Product`、`User`），不得包含特定业务逻辑
- **规范必须具有普遍适用性**：提交的规范应适用于所有 BuildAdmin 二次开发场景，而非某一特定项目的定制需求
- **保持中文**：本项目面向中文 BuildAdmin 开发者社区，所有文档使用中文编写

---

感谢你的贡献 🎉
