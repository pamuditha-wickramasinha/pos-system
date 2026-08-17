<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Printer;
use App\Models\Sale;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Rasterises a receipt for thermal printers that only speak raw ESC/POS.
 *
 * Two problems force this to be an image rendered by a *browser*:
 *
 *  1. A thermal printer's firmware font table covers a few Latin code pages only -
 *     it has no Sinhala glyphs, so ESC/POS *text* commands print Sinhala as "?????".
 *     Sending a picture sidesteps that: the printer just reproduces pixels.
 *
 *  2. Drawing that picture in PHP with GD/imagettftext() is not good enough either.
 *     GD renders through FreeType with no shaping engine (no HarfBuzz), so it maps
 *     codepoints to glyphs and lays them out in storage order. Sinhala needs real
 *     OpenType shaping: prepended vowel signs (ෙ ේ ො ෝ) reorder to the *left* of
 *     their consonant and conjuncts (ඤ්ඤ) ligate. Without it the characters are all
 *     present but malformed. A headless Chromium shapes via HarfBuzz, exactly like
 *     the on-screen receipt does, so what prints matches what staff see.
 */
class ReceiptImageRenderer
{
    /** Thermal printers are 203dpi: 576px across 80mm paper, 384px across 58mm. */
    protected const WIDTH_80MM = 576;

    protected const WIDTH_58MM = 384;

    /**
     * Render a sale's receipt and return the path to a PNG the caller must delete.
     */
    public function renderToPng(Sale $sale, Printer $printer): string
    {
        $sale->loadMissing('items.item');

        $html = View::make('sales.receipt-thermal', [
            'sale' => $sale,
            'company' => Company::where('id', 1)->where('status', true)->first(),
            'footerText' => (string) SiteSetting::query()->value('sales_invoice_footer_text'),
            ...$this->metrics($printer),
        ])->render();

        // Rough upper bound so the browser viewport is never shorter than the receipt.
        $estimatedHeight = 1200 + ($sale->items->count() * 140);

        return $this->screenshot($html, $this->metrics($printer)['widthPx'], $estimatedHeight);
    }

    /**
     * Render the "Test Print" page and return the path to a PNG the caller must delete.
     */
    public function renderTestToPng(Printer $printer): string
    {
        $html = View::make('sales.receipt-thermal-test', [
            'printer' => $printer,
            ...$this->metrics($printer),
        ])->render();

        return $this->screenshot($html, $this->metrics($printer)['widthPx'], 400);
    }

    /**
     * @return array{widthPx: int, base: int, pad: int}
     */
    protected function metrics(Printer $printer): array
    {
        $is80mm = $printer->paper_width >= 80;

        return [
            'widthPx' => $is80mm ? self::WIDTH_80MM : self::WIDTH_58MM,
            'base' => $is80mm ? 17 : 14,
            'pad' => $is80mm ? 12 : 8,
        ];
    }

    /**
     * Screenshot HTML in a headless browser, then trim the blank paper below the
     * content (the viewport is deliberately taller than the receipt).
     */
    protected function screenshot(string $html, int $width, int $height): string
    {
        $workDir = $this->makeWorkDir();

        try {
            File::put($workDir.DIRECTORY_SEPARATOR.'receipt.html', $html);
            // Copied in beside the HTML so the page's @font-face can load it as a
            // same-directory relative URL, which file:// pages are allowed to do.
            File::copy(resource_path('fonts/NotoSansSinhala.ttf'), $workDir.DIRECTORY_SEPARATOR.'font.ttf');

            $shot = $workDir.DIRECTORY_SEPARATOR.'receipt.png';

            $this->runBrowser([
                '--headless=new',
                '--disable-gpu',
                '--hide-scrollbars',
                '--no-first-run',
                '--no-default-browser-check',
                '--disable-extensions',
                '--force-device-scale-factor=1',
                '--default-background-color=FFFFFFFF',
                '--user-data-dir='.$workDir.DIRECTORY_SEPARATOR.'profile',
                '--window-size='.$width.','.min($height, 4000),
                '--screenshot='.$shot,
                $this->fileUrl($workDir.DIRECTORY_SEPARATOR.'receipt.html'),
            ]);

            if (! is_file($shot)) {
                throw new RuntimeException('The browser did not produce a receipt image.');
            }

            // Move the trimmed result out of the work dir before that dir is deleted.
            $final = tempnam(sys_get_temp_dir(), 'receipt').'.png';
            $this->trimTrailingWhitespace($shot, $final);

            return $final;
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    /**
     * @param  array<int, string>  $args
     */
    protected function runBrowser(array $args): void
    {
        $binary = $this->browserBinary();

        $process = new Process([$binary, ...$args]);
        $process->setTimeout((float) config('printing.browser_timeout', 30));

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            throw new RuntimeException('Timed out rendering the receipt in the browser.', 0, $e);
        }

        // Chrome exits non-zero in some headless paths even after writing the file,
        // so the caller checks for the screenshot rather than trusting the exit code.
    }

    protected function browserBinary(): string
    {
        foreach ((array) config('printing.browser_binaries', []) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'No Chrome or Edge browser was found for rendering receipts. '
            .'Set RECEIPT_BROWSER_BINARY in your .env to the full path of chrome.exe or msedge.exe.'
        );
    }

    protected function fileUrl(string $path): string
    {
        return 'file:///'.str_replace('\\', '/', $path);
    }

    /**
     * Crop the blank rows beneath the receipt so the printer does not feed a long
     * blank tail. Pixel work only - no text - so GD is perfectly adequate here.
     */
    protected function trimTrailingWhitespace(string $source, string $destination): void
    {
        $image = imagecreatefrompng($source);

        if ($image === false) {
            throw new RuntimeException('The rendered receipt image could not be read.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $lastContentRow = 0;

        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);

                // Anything meaningfully darker than paper counts as content.
                if ((($rgb >> 16) & 0xFF) < 240 || (($rgb >> 8) & 0xFF) < 240 || ($rgb & 0xFF) < 240) {
                    $lastContentRow = $y;
                    break 2;
                }
            }
        }

        $cropHeight = min($height, $lastContentRow + 12);

        $cropped = imagecreatetruecolor($width, max($cropHeight, 20));
        $white = imagecolorallocate($cropped, 255, 255, 255);
        imagefilledrectangle($cropped, 0, 0, $width, max($cropHeight, 20), $white);
        imagecopy($cropped, $image, 0, 0, 0, 0, $width, $cropHeight);

        imagepng($cropped, $destination);
        imagedestroy($cropped);
        imagedestroy($image);
    }

    protected function makeWorkDir(): string
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'receipt_'.bin2hex(random_bytes(6));
        File::ensureDirectoryExists($dir);

        return $dir;
    }
}
