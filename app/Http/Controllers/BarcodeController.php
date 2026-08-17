<?php

namespace App\Http\Controllers;

use Laminas\Barcode\Barcode as LBarcode;

class BarcodeController extends Controller
{
    public function show(string $code)
    {
        $barcodeOptions = [
            'text' => $code,
            'fontSize' => 10,
            'factor' => 2.5,
            'barHeight' => 10,
        ];

        $image = LBarcode::factory('code128', 'image', $barcodeOptions, [])->draw();

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);

        return response($contents, 200, ['Content-Type' => 'image/png']);
    }
}
