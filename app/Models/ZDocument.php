<?php

namespace App\Models;

use App\Enums\ZDocumentTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZDocument extends Model
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, SoftDeletes;

  /**
   * The "booted" method of the model.
   */
  protected static function booted(): void
  {
    static::creating(function (ZDocument $zDocument) {
      if (empty($zDocument->name)) {
        $zDocument->name = $zDocument->generateDefaultName();
      }
    });
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'user_id',
    'name',
    'number',
    'initial_balance',
    'activation_time',
    'sales',
    'final_balance',
    'type',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'type' => ZDocumentTypeEnum::class,
      'activation_time' => 'date',
    ];
  }


  /**
   * Generate a default name based on the document type and number.
   */
  private function generateDefaultName(): string
  {
    $number = $this->number ?? 'N/A';
    $type = $this->type ?? ZDocumentTypeEnum::SALE;

    // Convert enum to string if needed
    $typeValue = $type instanceof ZDocumentTypeEnum ? $type : ZDocumentTypeEnum::from($type ?? 'sale');

    return match ($typeValue) {
      ZDocumentTypeEnum::SALE => "Incasare clienti {$number}",
      ZDocumentTypeEnum::PAYMENT => "Plata {$number}",
    };
  }
}