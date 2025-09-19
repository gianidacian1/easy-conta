<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BalantaImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelImportService
{
	public function importBalantaFromFile(string $filePath): void
	{
		try {
			Excel::import(new BalantaImport, $filePath);
			Log::info("Balanta import completed successfully from: {$filePath}");
		} catch (\Exception $e) {
			Log::error("Balanta import failed: " . $e->getMessage(), [
				'file' => $filePath,
				'error' => $e->getTraceAsString()
			]);
			throw $e;
		}
	}

	public function importBalantaFromStorage(string $storagePath, string $disk = 'local'): void
	{
		try {
			Excel::import(new BalantaImport, $storagePath, $disk);
			Log::info("Balanta import completed successfully from storage: {$storagePath}");
		} catch (\Exception $e) {
			Log::error("Balanta import from storage failed: " . $e->getMessage(), [
				'storage_path' => $storagePath,
				'disk' => $disk,
				'error' => $e->getTraceAsString()
			]);
			throw $e;
		}
	}

	public function importBalantaFromUploadedFile($uploadedFile): void
	{
		try {
			Excel::import(new BalantaImport, $uploadedFile);
			Log::info("Balanta import completed successfully from uploaded file: " . $uploadedFile->getClientOriginalName());
		} catch (\Exception $e) {
			Log::error("Balanta import from uploaded file failed: " . $e->getMessage(), [
				'filename' => $uploadedFile->getClientOriginalName(),
				'error' => $e->getTraceAsString()
			]);
			throw $e;
		}
	}
}