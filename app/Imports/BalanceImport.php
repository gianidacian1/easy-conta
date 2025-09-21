<?php

namespace App\Imports;

use App\Models\Balance;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;

class BalanceImport implements ToModel, WithChunkReading
{
    use Importable;

    public function model(array $row)
    {
        // Based on your log, the structure is:
        // [0] = Cont, [1] = Denumirea contului,
        // [3] = Solduri initiale Debitoare, [4] = Solduri initiale Creditoare
        // [5] = Rulaje perioada Debitoare, [7] = Rulaje perioada Creditoare
        // [8] = Sume totale Debitoare, [9] = Sume totale Creditoare
        // [10] = Solduri finale Debitoare, [11] = Solduri finale Creditoare

        // Skip rows that don't have a numeric 'cont' in position 0
        if (!isset($row[0]) || !is_numeric($row[0])) {
            return null;
        }

        Log::info('Importing row with cont:', ['cont' => $row[0]]);
        $userId = auth()->user()->id;
        return Balance::updateOrCreate(
            ['cont' => $row[0]],
            [
                'user_id' => $userId,
                'denumirea_contului' => $row[1] ?? '',
                'solduri_initiale_an' => [
                    'debitoare' => $this->parseAmount($row[3] ?? 0),
                    'creditoare' => $this->parseAmount($row[4] ?? 0),
                ],
                'rulaje_perioada' => [
                    'debitoare' => $this->parseAmount($row[5] ?? 0),
                    'creditoare' => $this->parseAmount($row[7] ?? 0),
                ],
                'sume_totale' => [
                    'debitoare' => $this->parseAmount($row[8] ?? 0),
                    'creditoare' => $this->parseAmount($row[9] ?? 0),
                ],
                'solduri_finale' => [
                    'debitoare' => $this->parseAmount($row[10] ?? 0),
                    'creditoare' => $this->parseAmount($row[11] ?? 0),
                ],
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function parseAmount($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}

