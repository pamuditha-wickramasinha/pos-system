/**
 * PrinterBridge - shared client-side glue for silent receipt printing.
 *
 * A printer configured with connection_type = network|windows_local is printed
 * entirely by the server (see PrinterController::printSale / testPrint) - this
 * device just has to ask for it.
 *
 * A printer configured with connection_type = rawbt lives on THIS device (phone
 * with a printer on USB-OTG/Bluetooth/WiFi), so the server can only hand back the
 * raw ESC/POS bytes; this file hands them to the RawBT Android app via its
 * "rawbt:base64,..." URL scheme. RawBT must already be installed & the printer
 * connected inside that app - see docs/printer-setup.md.
 */
var PrinterBridge = (function () {
    var STORAGE_KEY = 'pos_device_printer';

    function getDevicePrinter() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function setDevicePrinter(printer) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(printer));
    }

    function clearDevicePrinter() {
        localStorage.removeItem(STORAGE_KEY);
    }

    function deviceLabel() {
        return ((navigator.platform || '') + ' ' + (navigator.userAgent || '')).substring(0, 190);
    }

    function logResult(base_url, saleId, printerId, status, message) {
        if (!saleId && !printerId) {
            // Nothing to attach this to (e.g. testing an unsaved printer form) - don't log.
            return;
        }
        $.post(base_url + 'printers/log_result', {
            sale_id: saleId,
            printer_id: printerId,
            status: status,
            message: message,
            device_label: deviceLabel(),
        });
    }

    // Handles the server's response: either it already printed (network/windows_local),
    // or it is asking us to deliver the bytes ourselves (rawbt).
    function handleServerResponse(base_url, result, saleId, printerId, onDone) {
        if (result && result.status === 'dispatch' && result.connection_type === 'rawbt') {
            try {
                window.location = 'rawbt:base64,' + result.payload;
                logResult(base_url, saleId, printerId, 'success', 'Dispatched to RawBT app.');
                onDone({ status: 'success', message: 'Sent to RawBT app on this device.' });
            } catch (e) {
                logResult(base_url, saleId, printerId, 'failed', String(e));
                onDone({ status: 'failed', message: String(e) });
            }
            return;
        }
        onDone(result);
    }

    // Test the connection details currently typed into the add/edit form, before saving.
    function testConnection(base_url, formSelector, onDone) {
        $.post(base_url + 'printers/test_connection', $(formSelector).serialize())
            .done(function (result) { handleServerResponse(base_url, result, null, null, onDone); })
            .fail(function (xhr) { onDone({ status: 'failed', message: xhr.responseText || 'Request failed.' }); });
    }

    function testPrint(base_url, printerId, onDone) {
        $.post(base_url + 'printers/test_print/' + printerId, { device_label: deviceLabel() })
            .done(function (result) { handleServerResponse(base_url, result, null, printerId, onDone); })
            .fail(function (xhr) { onDone({ status: 'failed', message: xhr.responseText || 'Request failed.' }); });
    }

    // Auto-print a completed sale using whichever printer this device is configured to use.
    // Calls onDone({status:'success'|'failed'|'skipped', message}). 'skipped' means no printer
    // is configured on this device yet - callers should fall back to the print preview popup.
    function printSale(base_url, saleId, onDone) {
        var printer = getDevicePrinter();
        if (!printer) {
            onDone({ status: 'skipped' });
            return;
        }

        $.post(base_url + 'printers/print_sale/' + saleId + '/' + printer.id, { device_label: deviceLabel() })
            .done(function (result) { handleServerResponse(base_url, result, saleId, printer.id, onDone); })
            .fail(function (xhr) { onDone({ status: 'failed', message: xhr.responseText || 'Request failed.' }); });
    }

    return {
        getDevicePrinter: getDevicePrinter,
        setDevicePrinter: setDevicePrinter,
        clearDevicePrinter: clearDevicePrinter,
        testPrint: testPrint,
        testConnection: testConnection,
        printSale: printSale,
    };
})();
