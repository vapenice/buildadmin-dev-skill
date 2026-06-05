param (
    [Parameter(Mandatory=$false)]
    [string]$Path = "app"
)

# BuildAdmin PHP 语法批量检测脚本
# 使用方法：
#   检测单个文件：.\check-syntax.ps1 -Path "app/admin/controller/Article.php"
#   检测整个目录：.\check-syntax.ps1 -Path "app/admin/controller"

$ErrorCount = 0
$CheckCount = 0

function Check-PhpFile {
    param ([string]$FilePath)
    
    $script:CheckCount++
    $output = & php -l $FilePath 2>&1
    
    if ($LASTEXITCODE -ne 0) {
        $script:ErrorCount++
        Write-Host ""
        Write-Host "[FAIL] $FilePath" -ForegroundColor Red
        Write-Host $output -ForegroundColor Yellow
    } else {
        Write-Host "[OK]   $FilePath" -ForegroundColor Green
    }
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " BuildAdmin PHP 语法检测器" -ForegroundColor Cyan
Write-Host " 检测路径: $Path" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if (Test-Path $Path -PathType Leaf) {
    # 单个文件
    Check-PhpFile -FilePath $Path
} elseif (Test-Path $Path -PathType Container) {
    # 目录：递归检测所有 .php 文件
    $phpFiles = Get-ChildItem -Path $Path -Recurse -Filter "*.php"
    foreach ($file in $phpFiles) {
        Check-PhpFile -FilePath $file.FullName
    }
} else {
    Write-Host "[ERROR] 路径不存在: $Path" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " 检测完成：共检测 $CheckCount 个文件" -ForegroundColor Cyan

if ($ErrorCount -gt 0) {
    Write-Host " 发现 $ErrorCount 个语法错误，请修复后再提交！" -ForegroundColor Red
    exit 1
} else {
    Write-Host " 全部通过！" -ForegroundColor Green
    exit 0
}
