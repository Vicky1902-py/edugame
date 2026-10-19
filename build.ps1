# Build frontend assets using high-speed local cache
$ErrorActionPreference = "Stop"
$buildDir = "C:\Users\vicky\AppData\Local\edugame-build"
$projectDir = "G:\My Drive\gaming"

if (-not (Test-Path "$buildDir\node_modules")) {
    Write-Host "Inisialisasi direktori build lokal..." -ForegroundColor Cyan
    New-Item -ItemType Directory -Force -Path $buildDir | Out-Null
    Copy-Item "$projectDir\package.json" "$buildDir\package.json"
    Copy-Item "$projectDir\tsconfig.json" "$buildDir\tsconfig.json"
    $env:PATH = "C:\Users\vicky\AppData\Local\dev-tools\node;$env:PATH"
    Push-Location $buildDir
    npm install --no-audit
    Pop-Location
}

Write-Host "Sinkronisasi resources/ ke build cache..." -ForegroundColor Cyan
Copy-Item -Recurse -Force "$projectDir\resources" "$buildDir\"

Write-Host "Menjalankan Vite build..." -ForegroundColor Cyan
$env:PATH = "C:\Users\vicky\AppData\Local\dev-tools\node;$env:PATH"
Push-Location $buildDir
npx vite build
Pop-Location
Write-Host "Build selesai! Output tersimpan di public/build/" -ForegroundColor Green
