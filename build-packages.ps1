[CmdletBinding()]
param(
    [ValidateSet("Both", "Plugin", "Theme")]
    [string]$Component = "Both"
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$repoRoot = (Resolve-Path -LiteralPath $PSScriptRoot).Path
$distRoot = Join-Path $repoRoot "dist"

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Get-WordPressVersion {
    param(
        [Parameter(Mandatory = $true)]
        [string]$HeaderFile
    )

    $content = [System.IO.File]::ReadAllText($HeaderFile)
    $match = [regex]::Match(
        $content,
        "^\s*(?:\*\s*)?Version:\s*([^\s]+)",
        [System.Text.RegularExpressions.RegexOptions]::IgnoreCase -bor
            [System.Text.RegularExpressions.RegexOptions]::Multiline
    )

    if (-not $match.Success) {
        throw "Header Version tidak ditemukan: $HeaderFile"
    }

    return $match.Groups[1].Value
}

function New-PortableWordPressZip {
    param(
        [Parameter(Mandatory = $true)]
        [string]$SourceDirectory,

        [Parameter(Mandatory = $true)]
        [string]$RootDirectoryName,

        [Parameter(Mandatory = $true)]
        [string]$Destination,

        [Parameter(Mandatory = $true)]
        [string]$RequiredEntry
    )

    $sourceRoot = (Resolve-Path -LiteralPath $SourceDirectory).Path.TrimEnd("\", "/")
    if (Test-Path -LiteralPath $Destination) {
        Remove-Item -LiteralPath $Destination -Force
    }

    $stream = [System.IO.File]::Open(
        $Destination,
        [System.IO.FileMode]::CreateNew,
        [System.IO.FileAccess]::ReadWrite,
        [System.IO.FileShare]::None
    )

    try {
        $archive = [System.IO.Compression.ZipArchive]::new(
            $stream,
            [System.IO.Compression.ZipArchiveMode]::Create,
            $false,
            [System.Text.Encoding]::UTF8
        )

        try {
            $files = Get-ChildItem -LiteralPath $sourceRoot -Recurse -File -Force |
                Sort-Object FullName

            foreach ($file in $files) {
                $relative = $file.FullName.Substring($sourceRoot.Length).TrimStart([char]92, [char]47)
                $entryName = $RootDirectoryName + "/" + ($relative -replace "\\", "/")

                [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                    $archive,
                    $file.FullName,
                    $entryName,
                    [System.IO.Compression.CompressionLevel]::Optimal
                ) | Out-Null
            }
        } finally {
            $archive.Dispose()
        }
    } finally {
        $stream.Dispose()
    }

    $verification = [System.IO.Compression.ZipFile]::OpenRead($Destination)
    try {
        $entryNames = @($verification.Entries | ForEach-Object { $_.FullName })
        $invalidPaths = @($entryNames | Where-Object { $_ -match "\\" })

        if ($invalidPaths.Count -gt 0) {
            throw "ZIP mengandung pemisah jalur Windows: $($invalidPaths[0])"
        }
        if ($entryNames -notcontains $RequiredEntry) {
            throw "File utama WordPress tidak ditemukan di ZIP: $RequiredEntry"
        }
    } finally {
        $verification.Dispose()
    }

    $package = Get-Item -LiteralPath $Destination
    Write-Host ("Paket valid: {0} ({1:N0} byte)" -f $package.FullName, $package.Length)
}

New-Item -ItemType Directory -Path $distRoot -Force | Out-Null

if ($Component -in @("Both", "Plugin")) {
    $pluginRoot = Join-Path $repoRoot "queen-alfalah-core"
    $pluginVersion = Get-WordPressVersion -HeaderFile (Join-Path $pluginRoot "queen-alfalah-core.php")
    New-PortableWordPressZip `
        -SourceDirectory $pluginRoot `
        -RootDirectoryName "queen-alfalah-core" `
        -Destination (Join-Path $distRoot "queen-alfalah-core-$pluginVersion.zip") `
        -RequiredEntry "queen-alfalah-core/queen-alfalah-core.php"
}

if ($Component -in @("Both", "Theme")) {
    $themeRoot = Join-Path $repoRoot "queen-alfalah"
    $themeVersion = Get-WordPressVersion -HeaderFile (Join-Path $themeRoot "style.css")
    New-PortableWordPressZip `
        -SourceDirectory $themeRoot `
        -RootDirectoryName "queen-alfalah" `
        -Destination (Join-Path $distRoot "queen-alfalah-$themeVersion.zip") `
        -RequiredEntry "queen-alfalah/style.css"
}
