<#
    POS Print Agent
    ---------------
    Bridges the gap between a hosted POS and a USB thermal printer.

    The browser cannot talk to USB hardware, and a remote server has no route to a
    printer sitting in your shop. So the server renders the receipt to raw ESC/POS
    bytes, the page POSTs them here, and this script hands them to the Windows print
    spooler in RAW mode - which is what gets them to the printer untouched.

    Run this on the PC the printer is plugged into:

        powershell -ExecutionPolicy Bypass -File pos-print-agent.ps1

    It listens on 127.0.0.1 only, so nothing outside this PC can reach it.

    Endpoints:
        GET  /ping   -> {"status":"ok"}  (for troubleshooting)
        POST /print  -> {"printer":"XP-80C","payload":"<base64 ESC/POS>"}

    Notes:
      * Uses a TcpListener rather than HttpListener so it needs no Administrator
        rights and no netsh urlacl reservation.
      * The printer does NOT need to be shared - we spool to it directly by name.
#>

param(
    [int] $Port = 9110
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

function Write-Log($message) {
    Write-Host ("[{0}] {1}" -f (Get-Date -Format 'HH:mm:ss'), $message)
}

function Get-DefaultPrinterName {
    $printer = Get-WmiObject -Class Win32_Printer -Filter 'Default = True'
    if ($printer) { return $printer.Name }
    return $null
}

# Minimal HTTP response writer. Every response carries CORS headers because the page
# making the request is served from another origin (the hosted POS), and Chrome's
# private-network-access check additionally requires Allow-Private-Network on the
# preflight before it will let a public page touch 127.0.0.1 at all.
function Send-Response($stream, [int] $status, [string] $statusText, [string] $body, [string] $origin) {
    $bodyBytes = [System.Text.Encoding]::UTF8.GetBytes($body)
    $allowOrigin = if ([string]::IsNullOrEmpty($origin)) { '*' } else { $origin }

    $headers = @(
        "HTTP/1.1 $status $statusText",
        "Content-Type: application/json; charset=utf-8",
        "Content-Length: $($bodyBytes.Length)",
        "Access-Control-Allow-Origin: $allowOrigin",
        "Access-Control-Allow-Methods: POST, GET, OPTIONS",
        "Access-Control-Allow-Headers: Content-Type",
        "Access-Control-Allow-Private-Network: true",
        "Access-Control-Max-Age: 86400",
        "Cache-Control: no-store",
        "Connection: close",
        "",
        ""
    ) -join "`r`n"

    $headerBytes = [System.Text.Encoding]::ASCII.GetBytes($headers)
    $stream.Write($headerBytes, 0, $headerBytes.Length)
    $stream.Write($bodyBytes, 0, $bodyBytes.Length)
    $stream.Flush()
}

function Convert-ToJsonBody([string] $status, [string] $message) {
    return (@{ status = $status; message = $message } | ConvertTo-Json -Compress)
}

$listener = New-Object System.Net.Sockets.TcpListener([System.Net.IPAddress]::Loopback, $Port)

try {
    $listener.Start()
} catch {
    Write-Host ""
    Write-Host "Could not listen on 127.0.0.1:$Port - $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Another program may already be using that port. Start with a different one:" -ForegroundColor Yellow
    Write-Host "    powershell -ExecutionPolicy Bypass -File pos-print-agent.ps1 -Port 9111" -ForegroundColor Yellow
    Write-Host "and set PRINT_AGENT_PORT to match in the server's .env." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "POS Print Agent listening on http://127.0.0.1:$Port" -ForegroundColor Green
Write-Host "Leave this window open while the POS is in use. Ctrl+C to stop." -ForegroundColor DarkGray
Write-Host ""

try {
    while ($true) {
        $client = $listener.AcceptTcpClient()
        $stream = $null
        $reader = $null

        try {
            $client.ReceiveTimeout = 15000
            $client.SendTimeout = 15000
            $stream = $client.GetStream()
            $reader = New-Object System.IO.StreamReader($stream, [System.Text.Encoding]::ASCII)

            $requestLine = $reader.ReadLine()

            if ([string]::IsNullOrWhiteSpace($requestLine)) {
                continue
            }

            $parts = $requestLine -split ' '
            $method = $parts[0]
            $path = if ($parts.Length -gt 1) { $parts[1] } else { '/' }

            # Headers, up to the blank line.
            $contentLength = 0
            $origin = ''

            while ($true) {
                $line = $reader.ReadLine()
                if ($null -eq $line -or $line -eq '') { break }

                if ($line -match '^(?i)Content-Length:\s*(\d+)') { $contentLength = [int] $Matches[1] }
                if ($line -match '^(?i)Origin:\s*(.+)$') { $origin = $Matches[1].Trim() }
            }

            if ($method -eq 'OPTIONS') {
                Send-Response $stream 204 'No Content' '' $origin
                continue
            }

            if ($method -eq 'GET' -and $path -like '/ping*') {
                Send-Response $stream 200 'OK' (Convert-ToJsonBody 'ok' 'POS Print Agent is running.') $origin
                continue
            }

            if ($method -ne 'POST' -or $path -notlike '/print*') {
                Send-Response $stream 404 'Not Found' (Convert-ToJsonBody 'failed' 'Unknown endpoint.') $origin
                continue
            }

            # Body is JSON holding base64, so it is pure ASCII - char count equals byte count.
            $body = ''

            if ($contentLength -gt 0) {
                $buffer = New-Object char[] $contentLength
                $read = 0

                while ($read -lt $contentLength) {
                    $chunk = $reader.Read($buffer, $read, $contentLength - $read)
                    if ($chunk -le 0) { break }
                    $read += $chunk
                }

                $body = [string]::new($buffer, 0, $read)
            }

            try {
                $request = $body | ConvertFrom-Json
                $printerName = $request.printer

                if ([string]::IsNullOrWhiteSpace($printerName)) {
                    $printerName = Get-DefaultPrinterName
                    if ([string]::IsNullOrWhiteSpace($printerName)) {
                        throw "No printer name was sent and this PC has no default printer."
                    }
                }

                if ([string]::IsNullOrWhiteSpace($request.payload)) {
                    throw "The request contained no payload to print."
                }

                $bytes = [Convert]::FromBase64String($request.payload)
                [RawPrinter]::Send($printerName, $bytes)

                Write-Log ("Printed {0} bytes to '{1}'." -f $bytes.Length, $printerName)
                Send-Response $stream 200 'OK' (Convert-ToJsonBody 'success' 'Sent to printer.') $origin
            } catch {
                # A failure from [RawPrinter]::Send arrives wrapped in PowerShell's
                # "Exception calling ..." boilerplate; the inner message is the useful
                # part and it is what the cashier ends up seeing in the toast.
                $message = if ($_.Exception.InnerException) { $_.Exception.InnerException.Message } else { $_.Exception.Message }
                Write-Log ("FAILED: {0}" -f $message)
                Send-Response $stream 500 'Internal Server Error' (Convert-ToJsonBody 'failed' $message) $origin
            }
        } catch {
            Write-Log ("Connection error: {0}" -f $_.Exception.Message)
        } finally {
            if ($reader) { $reader.Dispose() }
            if ($stream) { $stream.Dispose() }
            $client.Close()
        }
    }
} finally {
    $listener.Stop()
    Write-Host "POS Print Agent stopped."
}
