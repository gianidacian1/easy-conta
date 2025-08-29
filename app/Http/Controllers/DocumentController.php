<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\PdfOcrService;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

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

        $file = $request->file('document'); // single file

        if (!$file) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

       // Generate a custom name
        $customName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Store the file with the custom name in 'uploads' folder
        $path = $file->storeAs('uploads', $customName);

        // Example: get file info
        $fileInfo = [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getClientMimeType(),
        ];
        
        Document::create([
            'user_id' => auth()->user()->id,
            'file_name' => $customName  
        ]);
        // Optional: save file
        // $file->store('uploads');

        return response()->json([
            'message' => 'Upload successful',
            'file' => $fileInfo,
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
