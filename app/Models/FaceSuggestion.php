<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\FaceSuggestion.
 *
 * @property string $face_id
 * @property string $suggested_face_id
 * @property float  $confidence
 * @property Face   $face
 * @property Face   $suggestedFace
 */
class FaceSuggestion extends Model
{
	/**
	 * This model has no timestamps.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * @var string
	 */
	protected $table = 'face_suggestions';

	/**
	 * @var list<string>
	 */
	protected $fillable = [
		'face_id',
		'suggested_face_id',
		'confidence',
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'confidence' => 'float',
	];

	/**
	 * Return the face this suggestion belongs to.
	 *
	 * @return BelongsTo<Face,$this>
	 */
	public function face(): BelongsTo
	{
		return $this->belongsTo(Face::class, 'face_id', 'id');
	}

	/**
	 * Return the suggested face.
	 *
	 * @return BelongsTo<Face,$this>
	 */
	public function suggestedFace(): BelongsTo
	{
		return $this->belongsTo(Face::class, 'suggested_face_id', 'id');
	}
}
