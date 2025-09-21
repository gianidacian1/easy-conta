<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Balance extends Model
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory;

	protected $table = 'balances';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'user_id',
		'cont',
		'denumirea_contului',
		'solduri_initiale_an',
		'rulaje_perioada',
		'sume_totale',
		'solduri_finale'
	];
	protected $casts = [
		'solduri_initiale_an' => 'array',
		'rulaje_perioada' => 'array',
		'sume_totale' => 'array',
		'solduri_finale' => 'array'
	];
}