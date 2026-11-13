# Mở website trên Windows, tự chọn cổng trống (Windows hay chặn cổng 8000 do Hyper-V/WSL)
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path))
function Test-Port([int]$p) {
    try { $l = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Loopback, $p); $l.Start(); $l.Stop(); return $true } catch { return $false }
}
$port = (8000, 8080, 8888, 5000, 3000, 9000, 8765 | Where-Object { Test-Port $_ } | Select-Object -First 1)
if (-not $port) { Write-Host 'Không tìm được cổng trống.' -ForegroundColor Red; exit 1 }
Write-Host "Website: http://localhost:$port   (Ctrl+C để tắt)" -ForegroundColor Green
Write-Host 'Tài khoản thử (mật khẩu: password): admin@cinema.test | staff@cinema.test | user@cinema.test' -ForegroundColor Yellow
Start-Process "http://localhost:$port"
php artisan serve --host=127.0.0.1 --port=$port
