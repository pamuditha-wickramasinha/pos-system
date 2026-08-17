<#
    POS Launcher
    ------------
    One double-click to open the till: starts the print agent if it is not already
    running, waits until it actually answers, then opens the POS in the browser.

    Don't run this directly - double-click "Start POS.cmd", which holds your URL
    and port settings. See agent/README.md.
#>

param(
    # Address of the hosted POS, e.g. http://76.13.247.65/pos
    [Parameter(Mandatory = $true)]
    [string] $Url,

    # Must match the port the agent listens on.
    [int] $Port = 9110,

    # Open Chrome/Edge as a bare app window (no address bar or tabs), like a till.
    [switch] $Kiosk
)

$ErrorActionPreference = 'Stop'

function Test-AgentUp([int] $port) {
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:$port/ping" -UseBasicParsing -TimeoutSec 2
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
}

function Find-Browser {
    $candidates = @(
        "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
        "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
        "$env:LocalAppData\Google\Chrome\Application\chrome.exe",
        "$env:ProgramFiles\Microsoft\Edge\Application\msedge.exe",
        "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
    )

    foreach ($candidate in $candidates) {
        if (Test-Path -LiteralPath $candidate) { return $candidate }
    }

    return $null
}

Write-Host ""
Write-Host "  Starting POS" -ForegroundColor Cyan
Write-Host "  ------------" -ForegroundColor Cyan
Write-Host ""

# ---- 1. Print agent ------------------------------------------------------------

if (Test-AgentUp $Port) {
    Write-Host "  [ok]  Print agent already running on port $Port." -ForegroundColor Green
} else {
    $agentScript = Join-Path $PSScriptRoot 'pos-print-agent.ps1'

    if (-not (Test-Path -LiteralPath $agentScript)) {
        Write-Host "  [!!]  Cannot find pos-print-agent.ps1 next to this launcher." -ForegroundColor Red
        Write-Host "        Keep the whole 'agent' folder together." -ForegroundColor Yellow
        Start-Sleep -Seconds 10
        exit 1
    }

    Write-Host "  [..]  Starting print agent on port $Port..." -ForegroundColor Gray

    # Minimised rather than hidden, so the log stays reachable from the taskbar when
    # someone needs to see why a receipt did not come out.
    Start-Process -FilePath 'powershell.exe' `
        -ArgumentList '-ExecutionPolicy', 'Bypass', '-NoProfile', '-File', "`"$agentScript`"", '-Port', $Port `
        -WindowStyle Minimized | Out-Null

    # Starting the process is not the same as it being able to accept a job, so wait
    # for a real answer before handing the browser a page that might print instantly.
    $ready = $false

    for ($i = 0; $i -lt 20; $i++) {
        Start-Sleep -Milliseconds 500
        if (Test-AgentUp $Port) { $ready = $true; break }
    }

    if ($ready) {
        Write-Host "  [ok]  Print agent is up." -ForegroundColor Green
    } else {
        Write-Host "  [!!]  The print agent did not respond on port $Port." -ForegroundColor Red
        Write-Host "        Opening the POS anyway - receipts will not print until it is fixed." -ForegroundColor Yellow
        Write-Host "        Check the minimised agent window for the reason." -ForegroundColor Yellow
    }
}

# ---- 2. Browser ----------------------------------------------------------------

Write-Host "  [..]  Opening $Url" -ForegroundColor Gray

if ($Kiosk) {
    $browser = Find-Browser

    if ($browser) {
        Start-Process -FilePath $browser -ArgumentList "--app=$Url" | Out-Null
    } else {
        Write-Host "  [!!]  No Chrome or Edge found for app mode; using the default browser." -ForegroundColor Yellow
        Start-Process $Url | Out-Null
    }
} else {
    Start-Process $Url | Out-Null
}

Write-Host "  [ok]  Done." -ForegroundColor Green
Write-Host ""
Write-Host "  Leave the minimised 'POS Print Agent' window running." -ForegroundColor DarkGray
Write-Host ""

Start-Sleep -Seconds 3
