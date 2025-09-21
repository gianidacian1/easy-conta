<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ZDocumentExtractionService
{
    public function __construct()
    {
    }

    public function handle(string $filename = 'response_z.json')
    {
        $file = Storage::disk('public')->get($filename);
        $data = json_decode($file, true);
        $zData = [];

        foreach ($data['Blocks'] as $block) {
            if ($block['BlockType'] === 'LINE') {
                $text = trim($block['Text']);
                $zData[] = $text;
            }
        }

        return $this->extractZData($zData);
    }

    public function extractZData(array $data): array
    {
        $numerar = null;
        $contor = null;

        // 1. Find "Numerar" after "VANZARI:"
        $vanzariIndex = array_search('VANZARI:', $data);
        if ($vanzariIndex !== false) {
            // look for "Numerar" after VANZARI
            $numerarIndex = array_search('Numerar', array_slice($data, $vanzariIndex), true);
            if ($numerarIndex !== false) {
                $numerarIndex += $vanzariIndex; // adjust offset
                $numerar = $data[$numerarIndex + 1] ?? null;
            }
        }

        // 2. Find CONTOR
        $contorIndex = array_search('CONTOR 2', $data);
        if ($contorIndex !== false) {
            $contor = $data[$contorIndex + 1] ?? null;
        }

        return [
            'sales' => $numerar,
            'number' => $contor,
        ];
    }


    /////////////////Backup
    public function backupMethod(array $data)
    {
        $lastKey = null;
        foreach ($data['Blocks'] as $block) {
            if ($block['BlockType'] === 'LINE') {
                $text = trim($block['Text']);
                $data2[] = $text;
                // If it looks like a label (no numbers, just words)
                if (!preg_match('/\d/', $text)) {
                    $lastKey = $text;
                    if (!isset($zData[$lastKey])) {
                        $zData[$lastKey] = [];
                    }
                } else {
                    // It's a number/value
                    if ($lastKey !== null) {
                        // if key exists and is empty, assign single value
                        if (empty($zData[$lastKey])) {
                            $zData[$lastKey] = is_numeric($text) ? (float) $text : $text;
                        } else {
                            // if key already has value, convert to array
                            if (!is_array($zData[$lastKey])) {
                                $zData[$lastKey] = [$zData[$lastKey]];
                            }
                            $zData[$lastKey][] = is_numeric($text) ? (float) $text : $text;
                        }
                    }
                }
            }
        }
    }


    public function extractZData2(array $data): array
    {
        $numerar = null;
        $contor = null;

        // 1. Find "Numerar" after "VANZARI:"
        $vanzariIndex = array_search('VANZARI:', $data);
        if ($vanzariIndex !== false) {
            // look for "Numerar" after VANZARI
            $numerarIndex = array_search('Numerar', array_slice($data, $vanzariIndex), true);
            if ($numerarIndex !== false) {
                $numerarIndex += $vanzariIndex; // adjust offset
                $numerar = $data[$numerarIndex + 1] ?? null;
            }
        }

        // 2. Find CONTOR
        $contorIndex = array_search('CONTOR 2', $data);
        if ($contorIndex !== false) {
            $contor = $data[$contorIndex + 1] ?? null;
        }

        return [
            'numerar' => $numerar,
            'contor' => $contor,
        ];
    }
}