# CineBox - cài đặt và chạy trên Windows (bấm đúp setup.bat ở thư mục gốc)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

function Fail($msg) { Write-Host "`n[LỖI] $msg" -ForegroundColor Red; Read-Host 'Nhấn Enter để thoát'; exit 1 }
function Step($msg) { Write-Host "`n==> $msg" -ForegroundColor Cyan }

Step 'Kiểm tra PHP và Composer'
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { Fail 'Chưa có PHP 8.2+. Cài XAMPP/Laragon rồi thêm thư mục php vào PATH.' }
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) { Fail 'Chưa có Composer: https://getcomposer.org/Composer-Setup.exe' }
php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);"
if ($LASTEXITCODE -ne 0) { Fail "Cần PHP 8.2 trở lên. Bản hiện tại: $(php -r 'echo PHP_VERSION;')" }
Write-Host "PHP $(php -r 'echo PHP_VERSION;') - OK"

function Enable-PhpExtensions {
    $needed = 'pdo_sqlite','sqlite3','pdo_pgsql','pgsql','fileinfo','mbstring','openssl','curl','zip'
    $missing = @()
    foreach ($e in $needed) { if ((php -r "echo extension_loaded('$e') ? 1 : 0;") -ne '1') { $missing += $e } }
    if ($missing.Count -eq 0) { Write-Host 'Các extension PHP cần thiết đã bật - OK'; return }

    $phpDir = Split-Path -Parent (Get-Command php).Source
    $extDir = Join-Path $phpDir 'ext'
    $ini = (php -r "echo php_ini_loaded_file();")
    if (-not $ini) {
        $ini = Join-Path $phpDir 'php.ini'
        $tpl = Join-Path $phpDir 'php.ini-development'
        if (-not (Test-Path $tpl)) { $tpl = Join-Path $phpDir 'php.ini-production' }
        if (Test-Path $tpl) { Copy-Item $tpl $ini } else { [System.IO.File]::WriteAllText($ini, "") }
        Write-Host "Đã tạo $ini"
    }
    Write-Host "Bật extension: $($missing -join ', ') trong $ini"
    Copy-Item $ini "$ini.bak-cinebox" -Force

    $lines = [System.Collections.Generic.List[string]](Get-Content $ini)
    $hasExtDir = $false
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match '^\s*;?\s*extension_dir\s*=\s*"?ext"?\s*$' -or $lines[$i] -match '^\s*extension_dir\s*=') {
            $lines[$i] = "extension_dir = `"$extDir`""; $hasExtDir = $true; break
        }
    }
    if (-not $hasExtDir) { $lines.Add("extension_dir = `"$extDir`"") }

    foreach ($e in $missing) {
        if (-not (Test-Path (Join-Path $extDir "php_$e.dll"))) { Write-Host "  (bỏ qua ${e}: không có file php_$e.dll)" -ForegroundColor Yellow; continue }
        $done = $false
        for ($i = 0; $i -lt $lines.Count; $i++) {
            if ($lines[$i] -match "^\s*;\s*extension\s*=\s*(php_)?$e(\.dll)?\s*$") { $lines[$i] = "extension=$e"; $done = $true; break }
        }
        if (-not $done) { $lines.Add("extension=$e") }
    }
    try {
        [System.IO.File]::WriteAllLines($ini, $lines)
    } catch {
        Fail "Không ghi được $ini (cần quyền admin?). Hãy mở file này, bỏ dấu ; trước: $($missing -join ', ') rồi chạy lại."
    }
    foreach ($e in 'pdo_sqlite','pdo_pgsql') {
        if ((php -r "echo extension_loaded('$e') ? 1 : 0;") -ne '1') { Write-Host "  Cảnh báo: $e vẫn chưa bật được." -ForegroundColor Yellow }
    }
}
Enable-PhpExtensions

if (-not (Test-Path 'vendor\autoload.php')) {
    Step 'Cài thư viện (composer install) - lần đầu mất vài phút'
    composer install --no-interaction --prefer-dist
    if ($LASTEXITCODE -ne 0) { Fail 'composer install thất bại.' }
}

$db = Join-Path $root 'database\database.sqlite'
if (-not (Test-Path '.env') -or -not (Test-Path $db) -or (Get-Item $db).Length -eq 0) {
    Step 'Tạo .env, database và dữ liệu mẫu'
    composer run setup
    if ($LASTEXITCODE -ne 0) { Fail 'Tạo database thất bại.' }
}

& (Join-Path $root 'scripts\serve.ps1')
