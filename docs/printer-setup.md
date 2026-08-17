# Receipt Printer Setup

The POS can now print automatically, with no browser print dialog, from a PC or
a phone. How a given device gets there depends on how *that device's* printer
is connected. Configure printers under **Settings → Printers**.

There are three connection types. Pick one per physical printer.

## 1. Network printer (WiFi/Ethernet) — best option if you have it

If your printer has its own IP address and supports raw ESC/POS printing
(almost all thermal receipt printers do, usually on port 9100):

1. Settings → Printers → New Printer.
2. Connection Type: **Network**.
3. Enter the printer's IP address and port (default 9100).
4. Save, then use **Test Print** to confirm.
5. On the printers list, click **Use on This Device** — this remembers the
   choice in that browser only (via `localStorage`), so different devices can
   point at different printers.

This is the simplest option: the server prints directly, so it works from any
PC or phone on the network, no extra app needed on the client.

## 2. USB printer on this PC (the one you already have working)

Since this printer is plugged into the same Windows PC that runs the POS
server (XAMPP), the server can send print jobs straight to Windows' print
spooler — no popup, no extra software (no QZ Tray, no Java).

**One-time Windows setup — share the printer:**

1. Windows Settings → Bluetooth & devices → Printers & scanners (or
   Control Panel → Devices and Printers).
2. Right-click your receipt printer → Printer properties → **Sharing** tab.
3. Check **Share this printer**, give it a short share name (e.g. `POS58`),
   and Apply.
4. In the POS: Settings → Printers → New Printer → Connection Type:
   **Windows / USB (this PC)** → Shared Printer Name: the exact share name
   from step 3.
5. Save, then **Test Print**.
6. On the printers list, click **Use on This Device** from the PC's browser.

This works for the PC, and — because the *server* does the printing — it also
works for any phone/tablet on the network that completes a sale through this
same server, without anything installed on the phone.

## 3. Printer attached to a phone (USB‑OTG / Bluetooth / WiFi)

Use this only when the printer is physically with the phone itself (e.g. a
mobile cashier without a PC nearby), not shared through the server.

1. Install the free **RawBT** app from the Play Store on that phone.
2. Inside RawBT, connect to the printer (USB‑OTG cable, Bluetooth pairing, or
   WiFi — RawBT supports all three) and run RawBT's own test print to confirm
   the printer itself is reachable.
3. In the POS, from that phone's browser: Settings → Printers → New Printer
   → Connection Type: **Mobile (RawBT)** → Save.
4. Still on that phone, click **Use on This Device** for that printer.

The browser hands the receipt to RawBT via its `rawbt:` link; RawBT delivers
it over whichever connection you set up in step 2.

## How the auto-print decision works

Each browser/device remembers **one** printer choice (`Use on This Device`),
stored locally — nothing server-wide is forced on every device. When a POS
sale is completed:

- If this device has a printer configured, it prints silently through that
  printer and shows a success/failure toast. No popup.
- If not, it falls back to the original print-preview popup so the cashier
  can still print manually (or a printer can be set up later without
  breaking anything in the meantime).

Every server-side print attempt (network / Windows-local) is logged to the
`print_jobs` table (printer, sale, status, error) for troubleshooting.
RawBT-dispatched jobs are logged too, but since the server can't confirm
delivery, that status only reflects whether the *handoff* to RawBT succeeded.
