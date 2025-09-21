<?php

namespace App\Jobs;

use App\Services\ExcelImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected string $disk;

    public function __construct(string $filePath, string $disk = 'local')
    {
        $this->filePath = $filePath;
        $this->disk = $disk;
    }

    public function handle(): void
    {
        try {
            $excelService = new ExcelImportService();
            $excelService->importBalanceFromStorage($this->filePath, $this->disk);

            Log::info("Balanta import job completed successfully", [
                'file_path' => $this->filePath,
                'disk' => $this->disk
            ]);
        } catch (\Exception $e) {
            Log::error("Balanta import job failed", [
                'file_path' => $this->filePath,
                'disk' => $this->disk,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}