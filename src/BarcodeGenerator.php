<?php

namespace Isahaq\BarcodeQrCode;

use Exception;

class BarcodeGenerator
{
    protected $defaultOptions;
    protected $barcodeTypes;

    public function __construct()
    {
        $this->defaultOptions = [
            'width' => 2,
            'height' => 30,
            'foreground_color' => [0, 0, 0],
            'background_color' => [255, 255, 255],
            'text' => true,
            'text_size' => 12,
            'text_position' => 'bottom',
            'padding' => 10
        ];

        $this->barcodeTypes = [
            'CODE128' => [104, 'Code 128'],
            'CODE39' => [39, 'Code 39'],
            'EAN13' => [13, 'EAN-13'],
            'UPCA' => [12, 'UPC-A'],
            'EAN8' => [8, 'EAN-8']
        ];
    }

    public function generatePNG(string $type, string $code, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $options);

        try {
            $barcodeData = $this->encodeBarcode($type, $code);
            $image = $this->createBarcodeImage($barcodeData, $options);

            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);

            return $imageData;
        } catch (Exception $e) {
            throw new Exception('Failed to generate PNG barcode: ' . $e->getMessage());
        }
    }

    protected function encodeBarcode(string $type, string $code): array
    {
        if (!isset($this->barcodeTypes[$type])) {
            throw new Exception("Unsupported barcode type: $type");
        }

        $bars = [];
        switch ($type) {
            case 'CODE128':
                $bars = $this->encodeCode128($code);
                break;
            case 'CODE39':
                // Implement basic Code 39 encoding
                $validChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';
                $code = strtoupper($code);

                // Validate input
                if (!preg_match('/^[' . preg_quote($validChars, '/') . ']+$/', $code)) {
                    throw new Exception('Invalid characters in Code 39 barcode');
                }

                $bars = [];
                $patterns = [
                    '0' => [1, 0, 1, 1, 0, 1, 0, 0], '1' => [1, 1, 0, 1, 0, 0, 1, 0],
                    '2' => [1, 0, 0, 1, 0, 0, 1, 0], '3' => [1, 1, 0, 0, 1, 0, 0, 0],
                    '4' => [1, 0, 1, 1, 0, 0, 1, 0], '5' => [1, 1, 0, 1, 1, 0, 0, 0],
                    '6' => [1, 0, 0, 1, 1, 0, 0, 0], '7' => [1, 0, 1, 0, 0, 1, 0, 0],
                    '8' => [1, 1, 0, 0, 0, 1, 0, 0], '9' => [1, 0, 0, 0, 1, 0, 0, 0],
                    'A' => [1, 1, 0, 1, 0, 1, 0, 0], 'B' => [1, 0, 0, 1, 0, 1, 0, 0],
                    'C' => [1, 1, 0, 0, 1, 1, 0, 0], 'D' => [1, 0, 1, 1, 0, 1, 0, 0],
                    'E' => [1, 1, 0, 1, 1, 1, 0, 0], 'F' => [1, 0, 0, 1, 1, 1, 0, 0],
                    '-' => [1, 0, 1, 0, 1, 1, 0, 0], '.' => [1, 1, 0, 0, 1, 0, 1, 0],
                    ' ' => [1, 0, 0, 1, 0, 1, 1, 0], '$' => [1, 0, 1, 0, 1, 0, 1, 0],
                    '/' => [1, 0, 1, 0, 0, 1, 1, 0], '+' => [1, 0, 1, 1, 0, 0, 0, 0],
                    '%' => [1, 1, 0, 0, 0, 0, 1, 0]
                ];

                // Add start character *
                $bars = array_merge($bars, [1, 0, 1, 0, 0, 1, 1, 0]);

                // Encode each character
                foreach (str_split($code) as $char) {
                    $bars = array_merge($bars, $patterns[$char]);
                }

                // Add stop character *
                $bars = array_merge($bars, [1, 0, 1, 0, 0, 1, 1, 0]);

                return $bars;
                break;
                // Add other barcode type implementations here
        }

        return $bars;
    }

    protected function createBarcodeImage(array $bars, array $options): \GdImage
    {
        $width = count($bars) * $options['width'];
        $height = $options['height'];

        $image = imagecreatetruecolor($width + ($options['padding'] * 2), $height + ($options['padding'] * 2));

        // Set background color
        $bgColor = imagecolorallocate($image, ...$options['background_color']);
        $fgColor = imagecolorallocate($image, ...$options['foreground_color']);

        imagefill($image, 0, 0, $bgColor);

        // Draw bars
        $x = $options['padding'];
        foreach ($bars as $bar) {
            if ($bar) {
                imagefilledrectangle(
                    $image,
                    $x,
                    $options['padding'],
                    $x + $options['width'] - 1,
                    $options['padding'] + $height - 1,
                    $fgColor
                );
            }
            $x += $options['width'];
        }

        return $image;
    }

    protected function encodeCode128(string $code): array
    {
        // Implement Code 128 encoding logic here
        // This is a simplified version, you'll need to implement the full spec
        $bars = [];
        foreach (str_split($code) as $char) {
            $charCode = ord($char);
            // Add the bar pattern for each character
            $bars = array_merge($bars, $this->getCode128Pattern($charCode));
        }
        return $bars;
    }

    protected function getCode128Pattern(int $charCode): array
    {
        // Define the bar patterns for Code 128
        // This is a simplified version, you'll need to implement the full spec
        $patterns = [
            // Define patterns for each character
            // 1 represents a bar, 0 represents a space
            32 => [1, 1, 0, 1, 1, 0, 0],  // space
            // Add more patterns here
        ];

        return $patterns[$charCode] ?? array_fill(0, 7, 0);
    }
}
