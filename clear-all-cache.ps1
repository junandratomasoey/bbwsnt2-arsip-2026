# Jalankan dari root project E:\Herd\bbwsnt2-arsip\
# PowerShell: .\clear-all-cache.ps1

Write-Host "=== CLEAR ALL CACHE ===" -ForegroundColor Cyan

# 1. Hapus semua file cache Laravel
Write-Host "Menghapus cache files..." -ForegroundColor Yellow
$cacheDir = "storage\framework\cache\data"
if (Test-Path $cacheDir) {
    Get-ChildItem -Path $cacheDir -Recurse -File | Remove-Item -Force
    Write-Host "  ✓ Cache files dihapus" -ForegroundColor Green
}

# 2. Clear views
$viewsDir = "storage\framework\views"
if (Test-Path $viewsDir) {
    Get-ChildItem -Path $viewsDir -Filter "*.php" | Remove-Item -Force
    Write-Host "  ✓ View cache dihapus" -ForegroundColor Green
}

# 3. Artisan clear
Write-Host "Jalankan artisan clear..." -ForegroundColor Yellow
php artisan permission:cache-reset
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "✓ Selesai! Coba buka browser lagi." -ForegroundColor Green
