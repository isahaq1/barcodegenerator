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
            'C39' => 'Code 39',
            'C39+' => 'Code 39+',
            'C39E' => 'Code 39 Extended',
            'C39E+' => 'Code 39 Extended+',
            'C93' => 'Code 93',
            'S25' => 'Standard 2 of 5',
            'S25+' => 'Standard 2 of 5+',
            'I25' => 'Interleaved 2 of 5',
            'I25+' => 'Interleaved 2 of 5+',
            'C128' => 'Code 128',
            'C128A' => 'Code 128 A',
            'C128B' => 'Code 128 B',
            'C128C' => 'Code 128 C',
            'EAN2' => 'EAN 2',
            'EAN5' => 'EAN 5',
            'EAN8' => 'EAN 8',
            'EAN13' => 'EAN 13',
            'UPCA' => 'UPC-A',
            'UPCE' => 'UPC-E',
            'MSI' => 'MSI',
            'MSI+' => 'MSI+',
            'POSTNET' => 'POSTNET',
            'PLANET' => 'PLANET',
            'RMS4CC' => 'RMS4CC',
            'KIX' => 'KIX',
            'IMB' => 'IMB',
            'CODABAR' => 'Codabar',
            'CODE11' => 'Code 11',
            'PHARMA' => 'Pharma Code',
            'PHARMA2T' => 'Pharma Code Two-Track'
        ];
    }

    public function getSupportedTypes(): array
    {
        return $this->barcodeTypes;
    }

    public function isValidType(string $type): bool
    {
        return isset($this->barcodeTypes[$type]);
    }

    public function generatePNG(string $type, string $code, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $options);
        if (!$this->isValidType($type)) {
            throw new Exception("Unsupported barcode type: $type");
        }
        $bars = $this->encodeBarcode($type, $code);
        $image = $this->createBarcodeImage($bars, $options);
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        return $imageData;
    }

    protected function encodeBarcode(string $type, string $code): array
    {
        switch ($type) {
            case 'C39':
                return $this->encodeCode39($code);
            case 'C128':
                return $this->encodeCode128($code);
            case 'EAN13':
                return $this->encodeEAN13($code);
            case 'UPCA':
                return $this->encodeUPCA($code);
            default:
                throw new Exception("Encoding for barcode type '$type' is not implemented yet.");
        }
    }

    // Example: Code 39 encoding (simplified)
    protected function encodeCode39(string $code): array
    {
        // ... implement real Code 39 encoding here ...
        $bars = [];
        foreach (str_split($code) as $char) {
            $bars[] = 1; $bars[] = 0; // placeholder
        }
        return $bars;
    }

    // Example: Code 128 encoding (simplified)
    protected function encodeCode128(string $code): array
    {
        // ... implement real Code 128 encoding here ...
        $bars = [];
        foreach (str_split($code) as $char) {
            $bars[] = 1; $bars[] = 1; $bars[] = 0; // placeholder
        }
        return $bars;
    }

    // Example: EAN-13 encoding (simplified)
    protected function encodeEAN13(string $code): array
    {
        // ... implement real EAN-13 encoding here ...
        $bars = [];
        foreach (str_split($code) as $char) {
            $bars[] = 1; $bars[] = 0; $bars[] = 1; // placeholder
        }
        return $bars;
    }

    // Example: UPC-A encoding (simplified)
    protected function encodeUPCA(string $code): array
    {
        // ... implement real UPC-A encoding here ...
        $bars = [];
        foreach (str_split($code) as $char) {
            $bars[] = 0; $bars[] = 1; $bars[] = 1; // placeholder
        }
        return $bars;
    }

    protected function createBarcodeImage(array $bars, array $options): \GdImage
    {
        $width = count($bars) * $options['width'];
        $height = $options['height'];
        $image = imagecreatetruecolor($width + ($options['padding'] * 2), $height + ($options['padding'] * 2));
        $bgColor = imagecolorallocate($image, ...$options['background_color']);
        $fgColor = imagecolorallocate($image, ...$options['foreground_color']);
        imagefill($image, 0, 0, $bgColor);
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
}
