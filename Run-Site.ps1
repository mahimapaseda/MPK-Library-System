param(
    [switch]$Setup,
    [switch]$Seed
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Test-CommandExists {
    param([Parameter(Mandatory = $true)][string]$Name)

    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

function Invoke-Step {
    param(
        [Parameter(Mandatory = $true)][string]$Title,
        [Parameter(Mandatory = $true)][ScriptBlock]$Action
    )

    Write-Host "`n=== $Title ===" -ForegroundColor Cyan
    & $Action

    if ($LASTEXITCODE -ne 0) {
        throw "Step failed: $Title"
    }
}

if (-not (Test-CommandExists -Name 'php')) {
    throw 'PHP is not installed or not in PATH.'
}

if (-not (Test-CommandExists -Name 'composer')) {
    throw 'Composer is not installed or not in PATH.'
}

if (-not (Test-CommandExists -Name 'node')) {
    throw 'Node.js is not installed or not in PATH.'
}

if (-not (Test-CommandExists -Name 'npm')) {
    throw 'npm is not installed or not in PATH.'
}

Push-Location $PSScriptRoot

try {
    $needsSetup = $Setup -or -not (Test-Path '.\vendor\autoload.php') -or -not (Test-Path '.\node_modules')

    if ($needsSetup) {
        Invoke-Step -Title 'Running initial setup (composer run setup)' -Action {
            composer run setup
        }
    }

    if ($Seed) {
        Invoke-Step -Title 'Seeding database (php artisan db:seed)' -Action {
            php artisan db:seed
        }
    }

    Invoke-Step -Title 'Starting development services (composer run dev)' -Action {
        composer run dev
    }
}
finally {
    Pop-Location
}
