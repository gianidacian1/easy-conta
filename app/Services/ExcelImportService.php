<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BalanceImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelImportService
{
	public function importBalanceFromFile(string $filePath): void
	{
		try {
			Excel::import(new BalanceImport, $filePath);
			Log::info("Balanta import completed successfully from: {$filePath}");
		} catch (\Exception $e) {
			Log::error("Balanta import failed: " . $e->getMessage(), [
				'file' => $filePath,
				'error' => $e->getTraceAsString()
			]);
			throw $e;
		}
	}

	public function importBalanceFromStorage(string $storagePath, string $disk = 'local'): void
	{
		try {
			Excel::import(new BalanceImport, $storagePath, $disk);
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

	public function importBalanceFromUploadedFile($uploadedFile): void
	{
		try {
			Excel::import(new BalanceImport, $uploadedFile);
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