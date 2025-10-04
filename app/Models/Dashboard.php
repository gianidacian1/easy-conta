<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dashboard extends Model
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory, SoftDeletes;

	protected $table = 'dashboards';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'user_id',
		'title',
		'description',
		'widget_type',
		'widget_data',
		'position',
		'size',
		'is_active'
	];

	protected $casts = [
		'widget_data' => 'array',
		'is_active' => 'boolean'
	];

	/**
	 * Get the user that owns the dashboard widget.
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}
}