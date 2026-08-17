<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\Sale;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer as EscposPrinter;
use Throwable;

/**
 * Gets a Sale's receipt to a physical printer, over one of two delivery paths
 * (matching `printers.connection_type`):
 *  - network:      printer has its own IP, we open a raw socket to it ourselves. Needs the
 *                  server to be able to reach that IP, so it suits a printer on the same
 *                  LAN as the server rather than one in a shop behind NAT.
 *  - local_agent:  printer is on the USB port of the Windows counter PC while this app is
 *                  hosted elsewhere. We cannot reach it at all, so buildRawBytes() below
 *                  only builds the payload; the browser on that PC relays it to a loopback
 *                  agent which spools it to the printer. See agent/README.md.
 *
 * The receipt itself is always sent as a *picture* (see ReceiptImageRenderer), not as ESC/POS
 * text commands. Every one of these paths ends with raw ESC/POS bytes going straight to a
 * printer's own firmware, and that firmware's built-in font table has no Sinhala glyphs -
 * text commands would print Sinhala as "?????" regardless of which path got them there.
 * A raster image sidesteps that entirely: the printer just reproduces pixels.
 */
class ReceiptPrinterService
{
    public function __construct(protected ReceiptImageRenderer $renderer) {}

    /**
     * Print directly from the server. Only valid for 'network' printers.
     *
     * @return array{status: string, message?: string}
     */
    public function printFromServer(Sale $sale, Printer $printer): array
    {
        return $this->sendFromServer($printer, fn (EscposPrinter $escpos) => $this->printImage($escpos, $printer, $this->renderer->renderToPng($sale, $printer)));
    }

    /**
     * Build the raw ESC/POS byte string for a sale, for connection types the browser
     * itself must deliver ('local_agent').
     */
    public function buildRawBytes(Sale $sale, Printer $printer): string
    {
        return $this->buildBytes($printer, fn (EscposPrinter $escpos) => $this->printImage($escpos, $printer, $this->renderer->renderToPng($sale, $printer)));
    }

    /**
     * @return array{status: string, message?: string}
     */
    public function printTestFromServer(Printer $printer): array
    {
        return $this->sendFromServer($printer, fn (EscposPrinter $escpos) => $this->printImage($escpos, $printer, $this->renderer->renderTestToPng($printer)));
    }

    public function buildTestRawBytes(Printer $printer): string
    {
        return $this->buildBytes($printer, fn (EscposPrinter $escpos) => $this->printImage($escpos, $printer, $this->renderer->renderTestToPng($printer)));
    }

    /**
     * @param  callable(EscposPrinter): void  $draw
     * @return array{status: string, message?: string}
     */
    protected function sendFromServer(Printer $printer, callable $draw): array
    {
        if ($printer->connection_type !== 'network') {
            return ['status' => 'failed', 'message' => 'This printer must be printed from the browser, not the server.'];
        }

        try {
            $connector = new NetworkPrintConnector((string) $printer->ip_address, (int) ($printer->port ?: 9100));

            $escpos = new EscposPrinter($connector);
            $draw($escpos);
            $escpos->close();

            return ['status' => 'success'];
        } catch (Throwable $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    /**
     * @param  callable(EscposPrinter): void  $draw
     */
    protected function buildBytes(Printer $printer, callable $draw): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'escpos');

        try {
            $connector = new FilePrintConnector($tmpFile);
            $escpos = new EscposPrinter($connector);
            $draw($escpos);
            $escpos->close();

            return (string) file_get_contents($tmpFile);
        } finally {
            @unlink($tmpFile);
        }
    }

    protected function printImage(EscposPrinter $escpos, Printer $printer, string $tmpPng): void
    {
        try {
            $escpos->initialize();
            $escpos->setJustification(EscposPrinter::JUSTIFY_CENTER);
            $escpos->bitImage(EscposImage::load($tmpPng));
            $escpos->feed(2);

            if ($printer->cut_paper) {
                $escpos->cut();
            }

            if ($printer->open_cash_drawer) {
                $escpos->pulse();
            }
        } finally {
            @unlink($tmpPng);
        }
    }
}
