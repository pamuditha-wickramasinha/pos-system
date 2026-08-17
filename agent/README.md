# POS Print Agent

Lets a **hosted** POS print to a **USB** thermal printer at the counter.

## Why this exists, and why it polls

A browser cannot send raw bytes to a USB printer, and a server in a datacenter has no
network route to a printer sitting in your shop. So the agent runs on the counter PC and
**asks the server for work**:

```
Counter PC                                    Your server
----------                                    -----------
POS Print Agent  --- "any receipts for me?" -->  print_jobs queue
                 <-- ESC/POS bytes -----------
     |
     v
Windows spooler (RAW)  ->  XP-80C on USB
                 --- "printed / failed" ----->  job marked, cashier sees the result
```

Everything is **outbound**, which is what makes this robust:

- **No browser involved.** The obvious design — the page POSTing bytes to
  `http://127.0.0.1:9110` — is blocked by browsers. A page served from a plain-HTTP
  public origin may not open connections into the loopback address space
  (*Private Network Access*), and no header on the agent's side can permit it. That block
  is why this design polls instead.
- **Nothing to open on your network.** No port forwarding, no VPN, no inbound firewall
  rule, no printer exposed to the internet.
- **Any device can trigger a print.** A sale rung up on a phone still prints at the
  counter, because the phone never touches the printer — the server queues it.
- **Survives the move to HTTPS.** Nothing here depends on the page's origin.

The only other connection type is `network`, for a printer with its own IP that the
server can actually reach.

## Files

| File | What it's for |
|---|---|
| `Start POS.cmd` | **Everyday use.** Double-click: starts the agent if needed, then opens the POS. Set `POS_URL` at the top once per PC. |
| `agent-config.json` | The agent's server URL and token. **You create this** — it is not in git, because the token is a secret. |
| `agent-config.example.json` | Template to copy. |
| `start-agent.cmd` | Starts only the agent, with a visible log. Use when troubleshooting. |
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

3. In the POS: **Settings → Printers → Add**
   - **Connection Type**: `Windows / USB on the counter PC (via Print Agent)`
   - **Windows Printer Name**: the name from step 2 (e.g. `XP-80C`)
   - **Paper Width**: `80mm`
   - **Save**

   Test before saving is not possible for this type — the agent finds its work by the
   printer's token, which does not exist until the printer is saved.

4. Copy this `agent` folder to the PC, somewhere permanent like `C:\pos-agent`.

5. On the printers list, open **Action → Agent Setup** for that printer. It shows the
   exact `agent-config.json` to create, token included. Save it in the agent folder:

   ```json
   {
       "server_url": "http://76.13.247.65",
       "token": "<the token shown on that page>"
   }
   ```

6. Edit `Start POS.cmd` and set:

   ```bat
   set "POS_URL=http://76.13.247.65/pos"
   set "POS_KIOSK=1"
   ```

   `POS_KIOSK=1` opens a clean app window with no address bar or tabs, so staff can't
   browse away. `0` gives a normal browser window.

7. Double-click `Start POS.cmd`. The agent window should say:

   ```
   Connected. Printing for 'XP-80C'.
   ```

8. Back in the POS, use **Action → Test Print** on the printers list. Paper should come
   out, and the toast should confirm it actually printed — not merely that it was queued.

9. Click **Use on This Device** from that PC's browser so completed sales print there
   automatically.

10. To have it ready at login, press <kbd>Win</kbd>+<kbd>R</kbd>, run `shell:startup`,
    and put a shortcut to `Start POS.cmd` in that folder.

### More than one till

Give each till its **own printer record**, so each gets its own token and its own queue.
Don't reuse one token on two PCs — both agents would poll the same queue and whichever
polled first would print the other's receipts.

## Security

The token is that printer's whole authority: it can claim and read that printer's print
jobs, which contain receipt images. Treat it like a password.

- `agent-config.json` is gitignored. Never commit a real token.
- Tokens are per-printer, and a token can only touch its own printer's jobs.
- The agent only makes outbound requests, so nothing on the shop network is reachable
  from outside.
- Because you are currently serving the POS over plain HTTP, the token crosses the
  network in the clear. Moving to HTTPS fixes that, and is worth doing anyway since
  staff logins are equally exposed today.

## Troubleshooting

| Symptom | Cause |
|---|---|
| `Missing settings` and the agent exits | No `agent-config.json`, or it lacks `server_url`/`token`. See step 5. |
| `Cannot reach the server` | Wrong `server_url`, or no internet. The agent retries with backoff and recovers on its own. |
| Agent prints nothing, POS says *"Queued, but the print agent has not collected it"* | The agent isn't running, or its token belongs to a different printer than the one the sale was sent to. |
| `Could not open printer '...'. Win32 error 1801` | The printer name doesn't match Windows. 1801 means literally "invalid printer name" — recheck step 2. |
| Prints blank or garbled paper | Paper Width doesn't match the roll, or the printer isn't ESC/POS compatible. |
| Receipt printed twice | Two agents polling with the same token. Give each till its own printer record. |

Each job logs a line like `Printed job 46 - 15142 bytes to 'XP-80C'`. A queued job whose
agent dies mid-print is offered again after 90 seconds, so a crash costs a delay rather
than a lost receipt.

## Requirements

Windows with PowerShell 5.1 — built into Windows 10/11, nothing to install. No
Administrator rights needed, and the printer does **not** need to be shared: the agent
spools to it by name through the Windows raw print API.
