<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Services\PdfOcrService;
use Illuminate\Support\Facades\Storage;


class DocumentController
{
    public function index()
    {
        $documents = Document::where('user_id', auth()->user()->id)->get();
         return Inertia::render('documents/Index', [
            'documents' => $documents
         ]);
    }

    public function show()
    {
         return Inertia::render('documents/Show');
    }

    public function upload(Request $request)
    {
        // $request->validate([
        //     'document' => 'file',
        // ]);

        $file = $request->file('document'); // single file

        if (!$file) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        try {
            // Store the file with the custom name in 'uploads' folder
            $customName = 'Report ' . now()->format('Y-m-d') . time(). '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->put($customName, $file->getContent());

            // Create DB record
            Document::create([
                'user_id' => auth()->user()->id,
                'filename' => $customName
            ]);
        } catch (\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 400);
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
