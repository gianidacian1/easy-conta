<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Document;
use App\Models\ZDocument;
use Illuminate\Http\Request;
use App\Services\PdfOcrService;
use Illuminate\Support\Facades\Storage;
use App\Services\ZDocumentExtractionService;
use Throwable;

class ZDocumentController
{
  public function index()
  {
    $zDocuments = ZDocument::where('user_id', auth()->user()->id)->get();

    return Inertia::render('z/Index', [
      'zDocuments' => $zDocuments
    ]);
  }

  public function show()
  {
    return Inertia::render('z/Show');
  }

  public function upload(Request $request)
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


    return response()->json([
      'message' => 'Upload successful',
      'file' => $zData,
    ]);
  }

  public function process(Request $request)
  {
    $pdfPath = 'uploads/test.pdf';

    try {
      $ocrService = new PdfOcrService();
      // Set $mergePages = true if you want all pages merged
      $table = $ocrService->extractTables($pdfPath, false);

      return response()->json([
        'message' => 'OCR completed successfully',
        'table' => $table,
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }
}
