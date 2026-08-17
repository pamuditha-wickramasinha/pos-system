/**
 * PrinterBridge - shared client-side glue for silent receipt printing.
 *
 * A printer configured with connection_type = network is printed entirely by the
 * server (see PrinterController::printSale / testPrint) - this device just has to
 * ask for it.
 *
 * A printer configured with connection_type = local_agent is on THIS PC's USB port
 * while the app is hosted elsewhere, so the server can only build the raw ESC/POS
 * bytes and hand them back; we POST them to the print agent on this PC's own
 * loopback. A web page cannot reach a USB printer by itself - the agent is what
 * bridges that gap. See agent/README.md.
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

    // Hand the bytes to the print agent running on this PC. Kept deliberately blunt:
    // one POST, and whatever the agent says is what we report, since the agent is the
    // only thing here that can actually see the printer.
    function sendToLocalAgent(base_url, result, saleId, printerId, onDone) {
        var port = result.agent_port || 9110;
        var url = 'http://127.0.0.1:' + port + '/print';

        function fail(message) {
            logResult(base_url, saleId, printerId, 'failed', message);
            onDone({ status: 'failed', message: message });
        }

        // text/plain keeps this a CORS-simple request, so the only preflight that can
        // happen is Chrome's private-network one (which the agent also answers).
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain' },
            body: JSON.stringify({ printer: result.printer_target, payload: result.payload }),
        }).then(function (response) {
            return response.text().then(function (text) {
                if (!response.ok) {
                    fail('Print agent error: ' + (text || response.status));
                    return;
                }
                logResult(base_url, saleId, printerId, 'success', 'Sent to print agent.');
                onDone({ status: 'success', message: 'Sent to the printer on this PC.' });
            });
        }).catch(function () {
            // A refused/blocked loopback request lands here with no detail by design,
            // so spell out the likely cause rather than surfacing "Failed to fetch".
            fail('Could not reach the print agent on this PC (127.0.0.1:' + port + '). '
                + 'Make sure the POS Print Agent is running on this computer, then try again.');
        });
    }

    // Handles the server's response: either it already printed (network), or it is
    // asking us to deliver the bytes ourselves (local_agent).
    function handleServerResponse(base_url, result, saleId, printerId, onDone) {
        if (result && result.status === 'dispatch' && result.connection_type === 'local_agent') {
            sendToLocalAgent(base_url, result, saleId, printerId, onDone);
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
