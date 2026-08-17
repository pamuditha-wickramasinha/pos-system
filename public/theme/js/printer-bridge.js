/**
 * PrinterBridge - shared client-side glue for silent receipt printing.
 *
 * The browser never carries receipt bytes anywhere. It only asks the server to print,
 * and the server either does it itself (connection_type = network) or queues the job
 * for the counter PC's print agent to collect (connection_type = local_agent).
 *
 * That indirection is deliberate. The obvious design - the page POSTing the bytes
 * straight to the agent on 127.0.0.1 - is blocked by browsers: a page served from a
 * plain-HTTP public origin may not open connections into the loopback address space
 * (Private Network Access), and no header on the agent's side can permit it. Having the
 * agent poll outward avoids the browser entirely, so no CORS or private-network rule
 * applies, it needs no open port on the shop network, and a sale rung up on a phone
 * still prints at the counter.
 *
 * A queued job is only known to have printed once the agent reports back, so this polls
 * job_status rather than claiming success the moment the job is accepted.
 */
var PrinterBridge = (function () {
    var STORAGE_KEY = 'pos_device_printer';

    // The agent polls about once a second; allow for a slow receipt render on top.
    var POLL_INTERVAL_MS = 700;
    var POLL_TIMEOUT_MS = 20000;

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

    // Wait for the agent to collect the job and say what happened to it.
    function pollJob(base_url, jobId, onDone) {
        var startedAt = Date.now();

        function check() {
            $.get(base_url + 'printers/job_status/' + jobId)
                .done(function (result) {
                    if (result.status === 'success') {
                        onDone({ status: 'success', message: 'Receipt printed.' });
                        return;
                    }

                    if (result.status === 'failed') {
                        onDone({ status: 'failed', message: result.message || 'The printer reported an error.' });
                        return;
                    }

                    if (Date.now() - startedAt > POLL_TIMEOUT_MS) {
                        // Still pending: the job is safely queued and will print as soon
                        // as the agent runs, so this is a warning rather than a failure.
                        onDone({
                            status: 'pending',
                            message: 'Queued, but the print agent has not collected it. '
                                + 'Check that the POS Print Agent is running on the counter PC.',
                        });
                        return;
                    }

                    setTimeout(check, POLL_INTERVAL_MS);
                })
                .fail(function () {
                    onDone({ status: 'failed', message: 'Lost contact with the server while waiting for the printer.' });
                });
        }

        setTimeout(check, POLL_INTERVAL_MS);
    }

    // Either the server printed it already (network), or it queued it (local_agent).
    function handleServerResponse(base_url, result, onDone) {
        if (result && result.status === 'queued' && result.job_id) {
            pollJob(base_url, result.job_id, onDone);
            return;
        }

        onDone(result);
    }

    // Test the connection details currently typed into the add/edit form, before saving.
    function testConnection(base_url, formSelector, onDone) {
        $.post(base_url + 'printers/test_connection', $(formSelector).serialize())
            .done(function (result) { handleServerResponse(base_url, result, onDone); })
            .fail(function (xhr) { onDone({ status: 'failed', message: xhr.responseText || 'Request failed.' }); });
    }

    function testPrint(base_url, printerId, onDone) {
        $.post(base_url + 'printers/test_print/' + printerId, { device_label: deviceLabel() })
            .done(function (result) { handleServerResponse(base_url, result, onDone); })
            .fail(function (xhr) { onDone({ status: 'failed', message: xhr.responseText || 'Request failed.' }); });
    }

    // Auto-print a completed sale using whichever printer this device is configured to use.
    // Calls onDone({status:'success'|'failed'|'pending'|'skipped', message}). 'skipped' means
    // no printer is set on this device - callers fall back to the print preview popup.
    function printSale(base_url, saleId, onDone) {
        var printer = getDevicePrinter();
        if (!printer) {
            onDone({ status: 'skipped' });
            return;
        }

        $.post(base_url + 'printers/print_sale/' + saleId + '/' + printer.id, { device_label: deviceLabel() })
            .done(function (result) { handleServerResponse(base_url, result, onDone); })
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
