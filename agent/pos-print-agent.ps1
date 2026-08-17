<#
    POS Print Agent
    ---------------
    Bridges the gap between a hosted POS and a USB thermal printer.

    The agent asks the server "any receipts for me?" every second or so, prints whatever
    comes back, and reports the result. Everything is outbound, so:

      * no port has to be opened on the shop's network, and nothing is exposed to it
      * no browser is involved, so CORS and Private Network Access never apply
      * a sale rung up on any device - including a phone - prints at the counter

    Run this on the PC the printer is plugged into:

        powershell -ExecutionPolicy Bypass -File pos-print-agent.ps1 -ServerUrl http://76.13.247.65 -Token <token>

    Settings are normally read from agent-config.json beside this script, so you do not
    have to pass them each time. See agent/README.md.
#>

param(
    # Base address of the POS, e.g. http://76.13.247.65 or http://76.13.247.65/pos
    [string] $ServerUrl,

    # The printer's Agent Token, copied from Settings -> Printers in the POS.
    [string] $Token,

    # Seconds between polls when idle.
    [double] $PollSeconds = 1.5,

    # Path to the settings file. Defaults to agent-config.json beside this script.
    [string] $ConfigFile
)

$ErrorActionPreference = 'Stop'

Add-Type -TypeDefinition @'
using System;
using System.Runtime.InteropServices;

public class RawPrinter
{
    [StructLayout(LayoutKind.Sequential)]
    public class DOCINFOA
    {
        [MarshalAs(UnmanagedType.LPStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPStr)] public string pDataType;
    }

    [DllImport("winspool.Drv", EntryPoint = "OpenPrinterA", SetLastError = true, CharSet = CharSet.Ansi, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool OpenPrinter([MarshalAs(UnmanagedType.LPStr)] string szPrinter, out IntPtr hPrinter, IntPtr pd);

    [DllImport("winspool.Drv", EntryPoint = "ClosePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool ClosePrinter(IntPtr hPrinter);

    [DllImport("winspool.Drv", EntryPoint = "StartDocPrinterA", SetLastError = true, CharSet = CharSet.Ansi, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool StartDocPrinter(IntPtr hPrinter, int level, [In, MarshalAs(UnmanagedType.LPStruct)] DOCINFOA di);

    [DllImport("winspool.Drv", EntryPoint = "EndDocPrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool EndDocPrinter(IntPtr hPrinter);

    [DllImport("winspool.Drv", EntryPoint = "StartPagePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool StartPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.Drv", EntryPoint = "EndPagePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool EndPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.Drv", EntryPoint = "WritePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
    public static extern bool WritePrinter(IntPtr hPrinter, IntPtr pBytes, int dwCount, out int dwWritten);

    // RAW datatype is the whole point: it stops the driver from reinterpreting the
    // ESC/POS command stream as something to render.
    public static void Send(string printerName, byte[] bytes)
    {
        IntPtr hPrinter;

        if (!OpenPrinter(printerName, out hPrinter, IntPtr.Zero))
        {
            throw new Exception("Could not open printer '" + printerName + "'. Win32 error " +
                Marshal.GetLastWin32Error() + ". Check the name matches Windows exactly.");
        }

        try
        {
            DOCINFOA di = new DOCINFOA();
            di.pDocName = "POS Receipt";
            di.pDataType = "RAW";

            if (!StartDocPrinter(hPrinter, 1, di))
                throw new Exception("StartDocPrinter failed. Win32 error " + Marshal.GetLastWin32Error() + ".");

            try
            {
                if (!StartPagePrinter(hPrinter))
                    throw new Exception("StartPagePrinter failed. Win32 error " + Marshal.GetLastWin32Error() + ".");

                try
                {
                    IntPtr buffer = Marshal.AllocCoTaskMem(bytes.Length);

                    try
                    {
                        Marshal.Copy(bytes, 0, buffer, bytes.Length);
                        int written;

                        if (!WritePrinter(hPrinter, buffer, bytes.Length, out written))
                            throw new Exception("WritePrinter failed. Win32 error " + Marshal.GetLastWin32Error() + ".");

                        if (written != bytes.Length)
                            throw new Exception("Only " + written + " of " + bytes.Length + " bytes reached the spooler.");
                    }
                    finally { Marshal.FreeCoTaskMem(buffer); }
                }
                finally { EndPagePrinter(hPrinter); }
            }
            finally { EndDocPrinter(hPrinter); }
        }
        finally { ClosePrinter(hPrinter); }
    }
}
'@

function Write-Log($message, $colour = 'Gray') {
    Write-Host ("[{0}] {1}" -f (Get-Date -Format 'HH:mm:ss'), $message) -ForegroundColor $colour
}

function Get-DefaultPrinterName {
    $printer = Get-WmiObject -Class Win32_Printer -Filter 'Default = True'
    if ($printer) { return $printer.Name }
    return $null
}

# ---- Settings ------------------------------------------------------------------

if (-not $ConfigFile) {
    $ConfigFile = Join-Path $PSScriptRoot 'agent-config.json'
}

if (Test-Path -LiteralPath $ConfigFile) {
    $config = Get-Content -Raw -LiteralPath $ConfigFile | ConvertFrom-Json

    # Explicit parameters win over the file, so a one-off run can override it.
    if (-not $ServerUrl -and $config.server_url) { $ServerUrl = $config.server_url }
    if (-not $Token -and $config.token) { $Token = $config.token }
    if ($config.poll_seconds) { $PollSeconds = [double] $config.poll_seconds }
}

if ([string]::IsNullOrWhiteSpace($ServerUrl) -or [string]::IsNullOrWhiteSpace($Token)) {
    Write-Host ""
    Write-Host "  Missing settings." -ForegroundColor Red
    Write-Host ""
    Write-Host "  Create agent-config.json next to this script:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host '      {' -ForegroundColor DarkGray
    Write-Host '          "server_url": "http://76.13.247.65",' -ForegroundColor DarkGray
    Write-Host '          "token": "paste the printer Agent Token here"' -ForegroundColor DarkGray
    Write-Host '      }' -ForegroundColor DarkGray
    Write-Host ""
    Write-Host "  The token is on Settings -> Printers, via 'Agent Setup' on the printer's row." -ForegroundColor Yellow
    Write-Host ""
    Start-Sleep -Seconds 20
    exit 1
}

$base = $ServerUrl.TrimEnd('/')
$claimUrl = "$base/print-agent/jobs"

Write-Host ""
Write-Host "  POS Print Agent" -ForegroundColor Green
Write-Host "  Server: $base" -ForegroundColor DarkGray
Write-Host "  Leave this window open while the POS is in use. Ctrl+C to stop." -ForegroundColor DarkGray
Write-Host ""

# ---- Poll loop -----------------------------------------------------------------

$failureStreak = 0
$announced = $false

while ($true) {
    try {
        $response = Invoke-RestMethod -Uri $claimUrl -Method Post -TimeoutSec 20 `
            -Body @{ token = $Token } -ErrorAction Stop

        if (-not $announced) {
            Write-Log ("Connected. Printing for '{0}'." -f $response.printer) 'Green'
            $announced = $true
        }

        # Reaching the server at all clears the streak, even if there was no work.
        $failureStreak = 0

        foreach ($job in $response.jobs) {
            $printerName = $job.printer

            if ([string]::IsNullOrWhiteSpace($printerName)) {
                $printerName = Get-DefaultPrinterName
            }

            try {
                if ([string]::IsNullOrWhiteSpace($printerName)) {
                    throw "No printer name was set on this printer and this PC has no default printer."
                }

                $bytes = [Convert]::FromBase64String($job.payload)
                [RawPrinter]::Send($printerName, $bytes)

                Write-Log ("Printed job {0} - {1} bytes to '{2}'." -f $job.id, $bytes.Length, $printerName) 'Green'
                $result = @{ token = $Token; status = 'success' }
            } catch {
                # A .NET failure arrives wrapped in PowerShell's "Exception calling ..."
                # boilerplate; the inner message is what the cashier needs to read.
                $reason = if ($_.Exception.InnerException) { $_.Exception.InnerException.Message } else { $_.Exception.Message }
                Write-Log ("Job {0} FAILED: {1}" -f $job.id, $reason) 'Red'
                $result = @{ token = $Token; status = 'failed'; message = $reason }
            }

            try {
                Invoke-RestMethod -Uri "$claimUrl/$($job.id)/result" -Method Post -TimeoutSec 20 -Body $result -ErrorAction Stop | Out-Null
            } catch {
                # The job stays claimed; the server re-offers it after its stale timeout,
                # so a dropped report costs a delay rather than a lost receipt.
                Write-Log ("Could not report job {0}: {1}" -f $job.id, $_.Exception.Message) 'Yellow'
            }
        }
    } catch {
        $failureStreak++
        $announced = $false

        # Say it once, then stay quiet - a POS left open overnight should not fill the
        # window with the same line thousands of times.
        if ($failureStreak -eq 1) {
            Write-Log ("Cannot reach the server: {0}" -f $_.Exception.Message) 'Yellow'
            Write-Log "Retrying quietly. Check the URL and token if this persists." 'DarkGray'
        }
    }

    # Back off when the server is unreachable so a long outage is not a tight retry loop.
    $wait = if ($failureStreak -gt 0) { [Math]::Min(30, $PollSeconds * [Math]::Min($failureStreak, 10)) } else { $PollSeconds }
    Start-Sleep -Seconds $wait
}
