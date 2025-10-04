<?php

namespace App\Http\Controllers;

use Throwable;
use Inertia\Inertia;
use App\Models\Document;
use App\Models\ZDocument;
use Illuminate\Http\Request;
use App\Services\PdfOcrService;
use App\Enums\ZDocumentTypeEnum;
use Illuminate\Support\Facades\Storage;
use App\Services\ZDocumentExtractionService;

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

  public function update(Request $request, ZDocument $zDocument)
  {
    // Ensure user owns this Z document
    if ($zDocument->user_id !== auth()->id()) {
      abort(403);
    }

    $validated = $request->validate([
      'number' => 'required|string',
      'initial_balance' => 'required|numeric',
      'sales' => 'required|numeric',
      'final_balance' => 'required|numeric',
    ]);

    $user = auth()->user();

    // Store original values for comparison
    $originalSales = (string) $zDocument->sales;
    $originalInitialBalance = (string) $zDocument->initial_balance;
    $newSales = (string) $validated['sales'];
    $newInitialBalance = (string) $validated['initial_balance'];

    // Calculate the difference in sales/payment amount
    $salesDifference = bcsub($newSales, $originalSales, 2);

    // Calculate the difference in initial balance
    $initialBalanceDifference = bcsub($newInitialBalance, $originalInitialBalance, 2);

    try {
      // Update the Z document first
      $zDocument->update([
        'number' => $validated['number'],
        'initial_balance' => $newInitialBalance,
        'sales' => $newSales,
        'final_balance' => (string) $validated['final_balance'],
      ]);

      // Adjust user's initial balance based on document type and changes
      $userBalanceAdjustment = '0';

      if ($zDocument->type === ZDocumentTypeEnum::SALE) {
        // For sales: increase user balance by sales difference
        // Also adjust for initial balance difference
        $userBalanceAdjustment = bcadd($salesDifference, $initialBalanceDifference, 2);
      } else if ($zDocument->type === ZDocumentTypeEnum::PAYMENT) {
        // For payments: decrease user balance by sales difference (payment amount)
        // Also adjust for initial balance difference
        $userBalanceAdjustment = bcsub($initialBalanceDifference, $salesDifference, 2);
      }

      // Apply the adjustment to user's balance
      if (bccomp($userBalanceAdjustment, '0', 2) !== 0) {
        $newUserBalance = bcadd((string) $user->initial_balance, $userBalanceAdjustment, 2);
        $user->initial_balance = $newUserBalance;
        $user->save();
      }

      return redirect()->back()->with('success', 'Z document and balance updated successfully');
    } catch (Throwable $th) {
      \Log::error('Error updating Z document: ' . $th->getMessage());
      return redirect()->back()->with('error', 'Failed to update Z document. Please try again.');
    }
  }

    public function addPayment(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'name' => 'required|string',
            'payment' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);
        $user = auth()->user();
        $finalBalance = bcsub((string) $user->initial_balance, (string) $validated['payment'], 2);

        try {
            ZDocument::create([
                'user_id' => auth()->id(),
                'number' => $validated['number'],
                'name' => $validated['name'],
                'initial_balance' => (string) $user->initial_balance,
                'activation_time' => $validated['date'],
                'sales' => (string) $validated['payment'],
                'final_balance' => $finalBalance,
                'type' => ZDocumentTypeEnum::PAYMENT,
            ]);

            $user->initial_balance = $finalBalance;
            $user->save();

            return redirect()->back()->with('success', 'Payment added successfully');
        } catch (Throwable $th) {
            \Log::error('Error adding payment: ' . $th->getMessage());
            return redirect()->back()->with('error', 'Failed to add payment. Please try again.');
        }
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


    return redirect()->back()->with('success', 'Z document uploaded successfully. Balance updated.');
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
