[CmdletBinding()]
param(
    [string]$Message = "Sync Queen Al-Falah project updates",
    [switch]$SkipBuild
)

$ErrorActionPreference = "Stop"
$repoRoot = (Resolve-Path -LiteralPath $PSScriptRoot).Path
$requiredFiles = @(
    (Join-Path $repoRoot "queen-alfalah\style.css"),
    (Join-Path $repoRoot "queen-alfalah-core\queen-alfalah-core.php")
)

foreach ($requiredFile in $requiredFiles) {
    if (-not (Test-Path -LiteralPath $requiredFile -PathType Leaf)) {
        throw "Struktur project tidak valid; berkas wajib tidak ditemukan: $requiredFile"
    }
}

Push-Location $repoRoot
try {
    $remote = git remote get-url origin 2>$null
    if (-not $remote) {
        git remote add origin "https://github.com/hanhkx/smkqueen.git"
        $remote = git remote get-url origin
    }
    if ($remote -notmatch "github\.com[:/]hanhkx/smkqueen(?:\.git)?$") {
        throw "Remote origin bukan repository hanhkx/smkqueen: $remote"
    }

    if (-not $SkipBuild) {
        & powershell -ExecutionPolicy Bypass -File (Join-Path $repoRoot "build-packages.ps1")
        if ($LASTEXITCODE -ne 0) {
            throw "Pembuatan paket WordPress gagal."
        }
    }

    git add --all
    git diff --cached --quiet
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Tidak ada perubahan baru untuk dikirim."
        exit 0
    }

    git commit -m $Message
    if ($LASTEXITCODE -ne 0) { throw "Commit Git gagal dibuat." }

    $branch = git branch --show-current
    if (-not $branch) {
        throw "HEAD sedang detached; buat atau pilih branch sebelum sinkronisasi."
    }

    git push -u origin $branch
    if ($LASTEXITCODE -ne 0) {
        throw "Push gagal. Pastikan internet tersedia dan GitHub sudah terautentikasi. Commit lokal tetap aman."
    }

    Write-Host "Sinkronisasi selesai: https://github.com/hanhkx/smkqueen/tree/$branch"
} finally {
    Pop-Location
}

