<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Balance;
use Illuminate\Http\Request;
use App\Services\PdfOcrService;
use App\Services\ExcelImportService;
use Illuminate\Support\Facades\Storage;


class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Balance::where('user_id', auth()->user()->id);

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
            $q->where('cont', 'like', "%{$search}%")
                ->orWhere('denumirea_contului', 'like', "%{$search}%");
            });
        }

        // Paginate results (25 per page by default)
        $balances = $query->paginate($request->get('per_page', 25))
            ->withQueryString(); // Preserve query parameters

        return Inertia::render('balances/Index', [
            'balances' => $balances,
            'filters' => $request->only(['search', 'per_page'])
        ]);
    }

    public function show()
    {
        return Inertia::render('balances/Show');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'balance' => 'required|file|mimes:xls,xlsx',
        ]);

        $file = $request->file('balance'); // single file

        if (!$file) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        try {
            // Store the file with the custom name in 'uploads' folder
            $customName = 'Balante_de_verificare_ ' . now()->format('Y-m-d') . time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->put($customName, $file->getContent());

            // Create DB record
            $excelService = new ExcelImportService();
            $excelService->importBalanceFromUploadedFile($file);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }


        return response()->json([
            'message' => 'Upload successful',
            'file' => $customName,
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
