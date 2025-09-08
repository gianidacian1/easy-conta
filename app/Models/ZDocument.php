<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZDocument extends Model
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'user_id',
    'number',
    'initial_balance',
    'activation_time',
    'sales',
    'final_balance',
  ];
}