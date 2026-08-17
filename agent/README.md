# POS Print Agent

Lets a **hosted** POS print to a **USB** thermal printer at the counter.

## Why this exists

A browser cannot send raw bytes to a USB printer — no browser exposes that — and a
server in a datacenter has no network route to a printer sitting in your shop. So the
work is split:

```
Server (renders receipt -> ESC/POS bytes)
  |
  v  bytes returned to the page
Browser on the counter PC
  |
  v  POST http://127.0.0.1:9110/print
POS Print Agent  ->  Windows spooler (RAW)  ->  XP-80C on USB
```

The server never touches the printer, so nothing has to be shared, port-forwarded, or
exposed to the internet. The agent listens on `127.0.0.1` only — unreachable from any
other machine.

Use this when the printer is plugged into the counter PC by USB, which is the normal
case. The only other connection type is `network`, for a printer with its own IP that
the server can actually reach.

## Files

| File | What it's for |
|---|---|
| `Start POS.cmd` | **Everyday use.** Double-click: starts the agent if needed, then opens the POS. Edit the URL/port settings at the top once per PC. |
| `start-agent.cmd` | Starts only the agent, with a visible log window. Use when troubleshooting. |
| `pos-print-agent.ps1` | The agent itself. |
| `start-pos.ps1` | Launcher logic used by `Start POS.cmd`. |

## Setup on the counter PC

1. Confirm the printer works from Windows itself (Printer Properties → Print Test Page).
   If Windows can't print, the agent can't either.

2. Get the exact printer name:

   ```powershell
   Get-Printer | Select-Object Name, PortName
   ```

   Use the `Name` value verbatim — this is the single most common thing to get wrong.

3. Copy this `agent` folder to the PC, somewhere permanent like `C:\pos-agent`.

4. Open `Start POS.cmd` in Notepad and set the three values at the top:

   ```bat
   set "POS_URL=http://76.13.247.65/pos"
   set "AGENT_PORT=9110"
   set "POS_KIOSK=1"
   ```

   `POS_KIOSK=1` opens a clean app window with no address bar or tabs, so staff can't
   browse away. Set it to `0` for a normal browser window.

5. Double-click `Start POS.cmd`. Expect:

   ```
   [ok]  Print agent is up.
   [ok]  Done.
   ```

   The agent runs minimised — leave it there. Running the launcher again won't start a
   second copy; it detects the running one and just opens the browser.

6. To have it ready at login, press <kbd>Win</kbd>+<kbd>R</kbd>, run `shell:startup`,
   and put a shortcut to `Start POS.cmd` in that folder.

7. In the POS: **Settings → Printers → Add**
   - **Connection Type**: `Windows / USB on the counter PC (via Print Agent)`
   - **Windows Printer Name**: the name from step 2 (e.g. `XP-80C`)
   - **Paper Width**: `80mm`

   Click **Test**. The receipt should print.

8. On the printers list, from that PC's browser, click **Use on This Device** so
   completed sales print there automatically.

Repeat on every counter PC that has its own printer. Each PC runs its own agent; one
printer record can be shared if the Windows printer name is identical on each.

## Troubleshooting

| Symptom | Cause |
|---|---|
| `Could not reach the print agent on this PC` | Agent isn't running, or is on a different port. Visit <http://127.0.0.1:9110/ping> in the same browser — it should return `{"status":"ok"}`. |
| `Could not open printer '...'. Win32 error 1801` | The printer name doesn't match Windows. 1801 means literally "invalid printer name" — recheck step 2. |
| `Could not listen on 127.0.0.1:9110` | Another program holds the port. Set `AGENT_PORT=9111` in `Start POS.cmd` **and** `PRINT_AGENT_PORT=9111` in the server's `.env`, then `php artisan config:clear`. |
| Prints blank or garbled paper | Paper Width doesn't match the roll, or the printer isn't ESC/POS compatible. |
| Nothing happens, no error | Browser blocked the loopback call. Open DevTools → Console and look for a private-network or mixed-content error. |

Each job logs a line in the agent window like `Printed 15142 bytes to 'XP-80C'`. That's
the fastest way to tell whether a failure was on the server side or the printer side: no
line at all means the bytes never arrived.

### If you move the POS to HTTPS

Browsers then treat a call to `http://127.0.0.1` as insecure mixed content. Chrome and
Edge special-case loopback as a secure origin so printing keeps working; Firefox and
Safari do not. Over plain HTTP (as now) there is no issue.

The agent answers Chrome's private-network preflight
(`Access-Control-Allow-Private-Network`), so a public-origin page is allowed to reach
loopback at all.

## Requirements

Windows with PowerShell 5.1 — built into Windows 10/11, nothing to install. No
Administrator rights needed: the agent uses a TCP listener rather than `HttpListener`,
so no `netsh urlacl` reservation is required, and it spools by printer name through the
Windows raw print API, so **the printer does not need to be shared**.
