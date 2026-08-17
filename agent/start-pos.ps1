<#
    POS Launcher
    ------------
    One double-click to open the till: starts the print agent if it is not already
    running, then opens the POS in the browser.

    Don't run this directly - double-click "Start POS.cmd", which holds your settings.
    See agent/README.md.
#>

param(
    # Address of the POS page to open, e.g. http://76.13.247.65/pos
    [Parameter(Mandatory = $true)]
    [string] $Url,

    # Open Chrome/Edge as a bare app window (no address bar or tabs), like a till.
    [switch] $Kiosk
)

$ErrorActionPreference = 'Stop'

function Get-RunningAgent {
    # Matched on the script name in the command line: the agent is a powershell.exe like
    # any other, so there is nothing else to distinguish it by. $PID is excluded because
    # this launcher's own command line can contain the same text.
    $me = $PID

    return @(Get-WmiObject Win32_Process -Filter "Name='powershell.exe'" |
        Where-Object { $_.ProcessId -ne $me -and $_.CommandLine -like '*pos-print-agent.ps1*' })
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

if ((Get-RunningAgent).Count -gt 0) {
    Write-Host "  [ok]  Print agent already running." -ForegroundColor Green
} else {
    $agentScript = Join-Path $PSScriptRoot 'pos-print-agent.ps1'
    $configFile = Join-Path $PSScriptRoot 'agent-config.json'

    if (-not (Test-Path -LiteralPath $agentScript)) {
        Write-Host "  [!!]  Cannot find pos-print-agent.ps1 next to this launcher." -ForegroundColor Red
        Write-Host "        Keep the whole 'agent' folder together." -ForegroundColor Yellow
        Start-Sleep -Seconds 15
        exit 1
    }

    if (-not (Test-Path -LiteralPath $configFile)) {
        Write-Host "  [!!]  agent-config.json is missing - the agent needs the server URL" -ForegroundColor Red
        Write-Host "        and this printer's Agent Token." -ForegroundColor Red
        Write-Host "        Get them from Settings -> Printers -> Agent Setup in the POS." -ForegroundColor Yellow
        Start-Sleep -Seconds 15
        exit 1
    }

    Write-Host "  [..]  Starting print agent..." -ForegroundColor Gray

    # Minimised rather than hidden, so the log stays reachable from the taskbar when
    # someone needs to see why a receipt did not come out.
    Start-Process -FilePath 'powershell.exe' `
        -ArgumentList '-ExecutionPolicy', 'Bypass', '-NoProfile', '-File', "`"$agentScript`"" `
        -WindowStyle Minimized | Out-Null

    Start-Sleep -Seconds 2

    if ((Get-RunningAgent).Count -gt 0) {
        Write-Host "  [ok]  Print agent started." -ForegroundColor Green
    } else {
        Write-Host "  [!!]  The print agent stopped straight away." -ForegroundColor Red
        Write-Host "        Run start-agent.cmd to see why - most likely a bad token or URL." -ForegroundColor Yellow
    }
}

# ---- 2. Browser ----------------------------------------------------------------

Write-Host "  [..]  Opening $Url" -ForegroundColor Gray

$browser = Find-Browser

if ($Kiosk -and $browser) {
    # --app gives a bare window with no address bar or tabs, so staff cannot browse away.
    Start-Process -FilePath $browser -ArgumentList "--app=$Url" | Out-Null
} else {
    if ($Kiosk) {
        Write-Host "  [!!]  No Chrome or Edge found for app mode; using the default browser." -ForegroundColor Yellow
    }

    Start-Process $Url | Out-Null
}

Write-Host "  [ok]  Done." -ForegroundColor Green
Write-Host ""
Write-Host "  Leave the minimised 'POS Print Agent' window running." -ForegroundColor DarkGray
Write-Host ""

Start-Sleep -Seconds 3
