<?php

namespace App\Services;

use Aws\Textract\TextractClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class TextractService
{
	private TextractClient $textractClient;

	public function __construct()
	{
		$this->textractClient = new TextractClient([
			'version' => 'latest',
			'region' => config('aws.textract.region', 'us-east-1'),
			'credentials' => [
				'key' => config('aws.access_key_id'),
				'secret' => config('aws.secret_access_key'),
			],
		]);
	}

	public function analyzeDocument(string $filePath, string $bucket = null): array
	{
		dd(
			$this->textractClient
		);
		try {
			if ($bucket) {
				return $this->analyzeDocumentFromS3($filePath, $bucket);
			} else {
				return $this->analyzeDocumentFromLocal($filePath);
			}
		} catch (AwsException $e) {
			Log::error('AWS Textract error: ' . $e->getMessage());
			throw $e;
		}
	}

	public function analyzeDocumentFromS3(string $filePath, string $bucket): array
	{
		$result = $this->textractClient->analyzeDocument([
			'Document' => [
				'S3Object' => [
					'Bucket' => $bucket,
					'Name' => $filePath,
				],
			],
			'FeatureTypes' => ['TABLES', 'FORMS'],
		]);

		return $result->toArray();
	}

	public function analyzeDocumentFromLocal(string $filePath): array
	{
		$fileContents = Storage::get($filePath);

		$result = $this->textractClient->analyzeDocument([
			'Document' => [
				'Bytes' => $fileContents,
			],
			'FeatureTypes' => ['TABLES', 'FORMS'],
		]);

		return $result->toArray();
	}

	public function startDocumentAnalysis(string $filePath, string $bucket): string
	{
		$result = $this->textractClient->startDocumentAnalysis([
			'DocumentLocation' => [
				'S3Object' => [
					'Bucket' => $bucket,
					'Name' => $filePath,
				],
			],
			'FeatureTypes' => ['TABLES', 'FORMS'],
		]);

		return $result['JobId'];
	}

	public function getDocumentAnalysis(string $jobId): array
	{
		$result = $this->textractClient->getDocumentAnalysis([
			'JobId' => $jobId,
		]);

		return $result->toArray();
	}

	public function textractInterpreter()
	{
		$mainHeaders = [
			0 => "Cont",
			1 => "Denumirea contului",
			2 => "Solduri",
			3 => "initiale an",
			4 => "Sume",
			5 => "precedente",
			6 => "Rulaje",
			7 => "perioada",
			8 => "Sume",
			9 => "totale",
			10 => "Solduri",
			11 => "finale"
		];

		$secondaryHeaders = [
			"",
			"",
			"Debitoare",
			"Creditoare",
			"Debitoare",
			"Creditoare",
			"Debitoare",
			"Creditoare",
			"Debitoare",
			"Creditoare",
			"Debitoare",
			"Creditoare"
		];

		$file = Storage::disk('local')->get('response.json');

		$textractJson = json_decode($file, true);

		$parsedTables = $this->parseTextractTables($textractJson);
		dd($parsedTables);
		$data = [];
		foreach ($parsedTables as $pageIndex => $tables) {
			foreach ($tables as $table) {
				// check main headers
				if (
					isset($table['headers']) &&
					isset($table['rows']) &&
					$table['headers'] == $mainHeaders
				) {

					foreach ($table['rows'] as $rows) {
						if ($rows === $secondaryHeaders)
							continue;
						$data[] = $rows;
					}
				}
			}
		}

		$document = Document::find(6);
		$document->tables = json_encode($data);
		$document->save();
		echo "<pre>";
		var_dump($data);
		exit();
		$filePath = 'reports/test_ocr.pdf';

		// Get file contents from S3
		$fileContents = Storage::disk('public')->get($filePath);

		// Create a temporary stream
		$temp = tmpfile();
		fwrite($temp, $fileContents);
		fseek($temp, 0);

		// Send it via HTTP POST
		$response = Http::attach(
			'file',
			$temp,                  // pass the stream
			basename($filePath)
		)->post('http://127.0.0.1:8001/extract-tables/');

		// Close temp
		fclose($temp);

		$tables = $response->json();

		return $this->formatTables($tables['tables']);
	}

	public function formatTables(array $rawTables): array
	{
		$formatted = [];

		foreach ($rawTables as $index => $table) {
			// Flatten nested arrays Camelot sometimes gives
			$flatRows = [];
			foreach ($table as $row) {
				// If row is nested array, flatten
				if (is_array($row)) {
					$flatRow = [];
					foreach ($row as $cell) {
						// Remove empty strings, trim spaces
						$flatRow[] = trim($cell);
					}
					$flatRows[] = $flatRow;
				} else {
					// fallback
					$flatRows[] = [trim($row)];
				}
			}

			// Assume first row is header
			$headers = $flatRows[0] ?? [];
			$rows = array_slice($flatRows, 1);

			$formatted['table_' . ($index + 1)] = [
				'headers' => $headers,
				'rows' => $rows,
			];
		}

		return $formatted;
	}


	/**
	 * Parses AWS Textract response and returns structured tables.
	 *
	 * @param array $textractResponse AWS Textract JSON decoded as an array
	 * @return array
	 */
	public function parseTextractTables(array $textractResponse): array
	{
		$blocks = $textractResponse['Blocks'] ?? [];

		// Collect blocks by type and by Id
		$blockMap = [];
		foreach ($blocks as $block) {
			$blockMap[$block['Id']] = $block;
		}

		// Find TABLE blocks
		$tables = [];
		foreach ($blocks as $block) {
			if ($block['BlockType'] === 'TABLE') {

				$tables[] = $this->parseSingleTable($block, $blockMap);
			}
		}

		return [
			'tables' => $tables
		];
	}

	/**
	 * Parses a single table block into headers and rows.
	 *
	 * @param array $tableBlock
	 * @param array $blockMap
	 * @return array
	 */
	public function parseSingleTable(array $tableBlock, array $blockMap): array
	{
		$rows = [];
		$headers = [];
		$cells = [];

		// Get all CELL blocks related to this table
		foreach ($tableBlock['Relationships'] ?? [] as $relationship) {
			if ($relationship['Type'] === 'CHILD') {
				foreach ($relationship['Ids'] as $childId) {
					$child = $blockMap[$childId] ?? null;
					if ($child && $child['BlockType'] === 'CELL') {
						$rowIndex = $child['RowIndex'] - 1; // Textract is 1-indexed
						$colIndex = $child['ColumnIndex'] - 1;

						$text = $this->getTextFromBlock($child, $blockMap);

						$cells[$rowIndex][$colIndex] = $text;
					}
				}
			}
		}

		// Assume first row is header
		if (!empty($cells)) {
			ksort($cells);
			$firstRow = array_shift($cells);
			ksort($firstRow);
			$headers = array_values($firstRow);

			// Remaining rows
			$rows = [];
			foreach ($cells as $row) {
				ksort($row);
				$rows[] = array_values($row);
			}
		}

		return [
			'headers' => $headers,
			'rows' => $rows
		];
	}

	/**
	 * Recursively get text from a block and its CHILD relationships.
	 *
	 * @param array $block
	 * @param array $blockMap
	 * @return string
	 */
	public function getTextFromBlock(array $block, array $blockMap): string
	{
		if (isset($block['Text'])) {
			return $block['Text'];
		}

		$text = '';
		foreach ($block['Relationships'] ?? [] as $relationship) {
			if ($relationship['Type'] === 'CHILD') {
				foreach ($relationship['Ids'] as $childId) {
					$child = $blockMap[$childId] ?? null;
					if ($child) {
						$text .= $this->getTextFromBlock($child, $blockMap) . ' ';
					}
				}
			}
		}

		return trim($text);
	}
}