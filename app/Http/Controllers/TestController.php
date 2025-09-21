<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Document;
use App\Models\ZDocument;
use Illuminate\Http\Request;
use App\Services\PdfOcrService;
use App\Services\ExcelImportService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\ZDocumentExtractionService;
use thiagoalessio\TesseractOCR\TesseractOCR;


class TestController
{
    public function test()
    {
        $file = Storage::disk('public')->get('response_z.json');

        $data = json_decode($file, true);

        $zData = [];
        $lastKey = null;
        $data2 = [];

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

        $service = new ZDocumentExtractionService();
        $finalData = $service->handle();
        dd($finalData);
        // $file = Storage::disk('s3')->get('response_z.json');
        // $data = json_decode($file, true);
        // $zData = [];
        // $lastKey = null;
        // $data2 = [];
        // foreach ($data['Blocks'] as $block) {
        //     if ($block['BlockType'] === 'LINE') {
        //         $text = trim($block['Text']);
        //         $data2[] = $text;
        //         // If it looks like a label (no numbers, just words)
        //         if (!preg_match('/\d/', $text)) {
        //             $lastKey = $text;
        //             if (!isset($zData[$lastKey])) {
        //                 $zData[$lastKey] = [];
        //             }
        //         } else {
        //             // It's a number/value
        //             if ($lastKey !== null) {
        //                 // if key exists and is empty, assign single value
        //                 if (empty($zData[$lastKey])) {
        //                     $zData[$lastKey] = is_numeric($text) ? (float) $text : $text;
        //                 } else {
        //                     // if key already has value, convert to array
        //                     if (!is_array($zData[$lastKey])) {
        //                         $zData[$lastKey] = [$zData[$lastKey]];
        //                     }
        //                     $zData[$lastKey][] = is_numeric($text) ? (float) $text : $text;
        //                 }
        //             }
        //         }
        //     }
        // }

        $service = new ZDocumentExtractionService();
        $data = $service->handle();

        $user = auth()->user();
        try {
            $finalBalance = $user->initial_balance + $data['sales'];
            $document = ZDocument::create([
                'user_id' => $user->id,
                'number' => $data['number'],
                'initial_balance' => $user->initial_balance,
                'activation_time' => now(),
                'sales' => $data['sales'],
                'final_balance' => $finalBalance,
            ]);

            $user->initial_balance = $finalBalance;
            $user->save();
        } catch (Throwable $th) {
            dd($th->getMessage());
        }

        dd(
            $data
        );
    }

    public function extractZData($array)
    {
        // 1. Get first Numerar if VANZARI exists
        $numerar = null;
        if (array_key_exists('VANZARI:', $array) && array_key_exists('Numerar', $array)) {
            $numerarValues = $array['Numerar'];
            $numerar = is_array($numerarValues) ? reset($numerarValues) : null;
        }

        // 2. Get CONTOR value
        $contor = null;
        if (isset($array['-'][0]) && str_contains($array['-'][0], 'CONTOR')) {
            $contor = $array['-'][1] ?? null;
        }

        return [
            'numerar' => $numerar,
            'contor' => (int) $contor,
        ];
    }


}