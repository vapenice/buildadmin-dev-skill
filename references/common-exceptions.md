# BuildAdmin CRUD 常见报错速查手册

## 1. 后端报错

### 1.1 PHP Fatal Error: Declaration of ... must be compatible with ...
**原因**：子类 override 方法的签名与父类/Trait 不兼容（PHP 8 严格执行）
**症状**：系统 500，日志中有 `E_COMPILE_ERROR` 或 `Declaration of ... must be compatible`
**解决**：
1. 查阅父类 `Backend` 或 Trait `Backend` 中对应方法的原始签名
2. 确保子类方法签名完全一致（尤其是 `: void` 返回值类型）
```php
// 正确
public function index(): void { parent::index(); }
```

---

### 1.2 Cannot use object of type Model as array
**原因**：将 ThinkPHP Model 对象作为数组使用
**症状**：PHP Fatal Error，通常出现在对查询结果进行 `$result['field']` 访问时
**解决**：在访问前调用 `->toArray()`
```php
$item = Article::find($id)->toArray();
echo $item['title']; // ✅
```

---

### 1.3 SQLSTATE[1267]: Illegal mix of collations
**原因**：两张表 JOIN 时，关联字段的字符集排序规则（Collation）不一致
**症状**：SQL 查询报 `Illegal mix of collations` 错误
**解决**：
- 方案 1（推荐）：改用 `with()` 预载入替代 JOIN
- 方案 2：统一两表关联字段的 Collation 为 `utf8mb4_unicode_ci`

---

### 1.4 Column 'xxx' in field list is ambiguous
**原因**：多表 JOIN 时，两表都有同名字段，未使用表前缀消歧
**症状**：SQL 报 `Ambiguous` 错误，常见于 `status`、`id`、`name`、`create_time` 字段
**解决**：在 `field()` 和 `order()` 中显式带表名前缀
```php
->field('article.status, category.name as category_name')
->order('article.create_time', 'desc')
```

---

### 1.5 接口返回空内容或 success/error 失效
**原因**：`try-catch` 捕获了 `HttpResponseException` 但未重新抛出
**症状**：调用 `$this->success()` 或 `$this->error()` 后，前端收不到任何响应体
**解决**：在 catch 块中重新抛出该异常
```php
} catch (\think\exception\HttpResponseException $e) {
    throw $e;
}
```

---

### 1.6 $preExcludeFields 类型冲突导致 Fatal Error
**原因**：在子类顶部声明了带类型的 `protected array $preExcludeFields`，而父类已有同名属性
**症状**：系统 500，日志有属性类型冲突相关 Fatal Error
**解决**：删除类顶部的属性声明，改为在 `initialize()` 中动态赋值

---

## 2. 前端报错

### 2.1 TypeError: Cannot read properties of undefined (reading 'xxx')
**原因**：访问后端返回数据时未做防御性判断
**症状**：页面白屏，控制台有 `Cannot read properties of undefined`
**解决**：在模板和 JS 中使用 `?.` 可选链运算符
```html
<!-- ✅ -->
<span>{{ scope.row?.category?.name }}</span>
```

---

### 2.2 baTable 列不渲染 / 表单字段缺失
**原因**：`column` 配置中 `prop` 字段名与后端返回的字段名不一致，或 `render` 类型错误
**解决**：
1. 核对后端接口返回的实际字段名（`console.log(res.data)` 检查）
2. 核对 `render` 支持的类型：`'tag'` / `'image'` / `'datetime'` / `'switch'` 等

---

### 2.3 TypeScript 类型报错 (Property 'xxx' does not exist on type 'any')
**原因**：接口响应类型使用了 `any`，失去类型推导能力
**解决**：为所有接口定义明确的 `interface` 类型

---

## 3. CRUD 生成相关

### 3.1 生成的表单没有出现图片上传组件
**原因**：字段名后缀不正确
**解决**：单图字段命名为 `xxx_image` 或 `xxx_avatar`，多图命名为 `xxx_images`

### 3.2 状态字段生成了普通输入框而非单选框
**原因**：字段名后缀或类型不符合规范
**解决**：
- 字段名应以 `status` / `state` / `type` 结尾
- 或使用 MySQL `enum` 类型
- 注释应按字典格式书写：`状态:0=禁用,1=启用`

### 3.3 Switch 开关显示为普通输入框
**原因**：字段名后缀不匹配
**解决**：字段名应以 `_switch` 或 `_toggle` 结尾
