<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PdfOcrService
{
    protected string $tesseractBinary;

    public function __construct()
    {
        // Adjust path for your Windows installation
        $this->tesseractBinary = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';
    }

    /**
     * Extract tables from a multi-page PDF.
     *
     * @param string $pdfPath Relative path on local disk, e.g., 'uploads/test.pdf'
     * @param bool $mergePages If true, all pages merged into one table
     * @return array
     */
    public function extractTables(string $pdfPath, bool $mergePages = false): array
    {
        if (!Storage::disk('local')->exists($pdfPath)) {
            throw new \Exception("PDF file does not exist: {$pdfPath}");
        }

        $pdfFullPath = Storage::disk('local')->path($pdfPath);
        $pdf = new Pdf($pdfFullPath);
        $pages = $pdf->getNumberOfPages();
        $allTables = [];

        for ($i = 1; $i <= $pages; $i++) {
            $imageRelative = "uploads/ocr_page_{$i}.png";
            $imageFull = Storage::disk('local')->path($imageRelative);

            // Convert page to image
            $pdf->setPage($i)->saveImage($imageFull);

            // OCR text extraction
            $text = (new TesseractOCR($imageFull))
                ->executable($this->tesseractBinary)
                ->lang('eng')
                ->run();

            // Convert text into table
            $rows = explode("\n", $text);
            $pageTable = [];
            foreach ($rows as $row) {
                $columns = preg_split('/\s{2,}/', $row); // split on multiple spaces
                $pageTable[] = $columns;
            }

            if ($mergePages) {
                $allTables = array_merge($allTables, $pageTable);
            } else {
                $allTables[$i] = $pageTable;
            }

            // Optional: delete temp image to save storage
            if (Storage::disk('local')->exists($imageRelative)) {
                Storage::disk('local')->delete($imageRelative);
            }
        }

        return $allTables;
    }
}
