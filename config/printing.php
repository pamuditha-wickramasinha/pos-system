<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Headless browser binary
    |--------------------------------------------------------------------------
    |
    | Thermal receipts are rasterised by rendering HTML in a headless Chromium
    | browser (see App\Services\ReceiptImageRenderer). A browser is used because
    | Sinhala - like every complex script - needs OpenType shaping (HarfBuzz) to
    | reorder prepended vowel signs and build conjuncts. PHP's GD/FreeType has no
    | shaping engine, so drawing the text with imagettftext() produces malformed
    | Sinhala even though the characters themselves are correct.
    |
    | The first binary that exists is used. Edge ships with Windows, so it acts
    | as the fallback when Chrome is not installed.
    |
    */

    'browser_binaries' => array_values(array_filter([
        env('RECEIPT_BROWSER_BINARY'),
        'C:\Program Files\Google\Chrome\Application\chrome.exe',
        'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
        'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
        'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ])),

    /*
    | Seconds to wait for the browser to produce a screenshot before giving up.
    */
    'browser_timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | Local print agent port
    |--------------------------------------------------------------------------
    |
    | Loopback port that the counter PC's print agent listens on, for printers
    | with connection_type = local_agent. The browser on that PC posts the
    | ESC/POS bytes to http://127.0.0.1:<port>/print. Must match the -Port the
    | agent was started with (see agent/README.md).
    |
    */

    'agent_port' => (int) env('PRINT_AGENT_PORT', 9110),

];
