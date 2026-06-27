# Jalankan dari root project E:\Herd\bbwsnt2-arsip\
# PowerShell: .\cleanup-models.ps1

Write-Host "Menghapus file model multi-class lama..." -ForegroundColor Yellow

$oldFiles = @(
    "app\Models\DocumentArchive.php",
    "app\Models\Knowledge.php",
    "app\Models\ProjectOp.php",
    "app\Models\System.php"
)

foreach ($f in $oldFiles) {
    if (Test-Path $f) {
        Remove-Item $f -Force
        Write-Host "  Dihapus: $f" -ForegroundColor Green
    } else {
        Write-Host "  Skip (sudah tidak ada): $f" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "Jalankan:" -ForegroundColor Cyan
Write-Host "  composer dump-autoload" -ForegroundColor White
Write-Host "  php artisan optimize:clear" -ForegroundColor White
