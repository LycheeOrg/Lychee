<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Models\Extensions\HasRandomIDAndLegacyTimeBasedID;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Face.
 *
 * @property string                         $id
 * @property string                         $photo_id
 * @property string|null                    $person_id
 * @property float                          $x
 * @property float                          $y
 * @property float                          $width
 * @property float                          $height
 * @property float                          $confidence
 * @property string|null                    $crop_token
 * @property bool                           $is_dismissed
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 * @property Photo                          $photo
 * @property Person|null                    $person
 * @property Collection<int,FaceSuggestion> $suggestions
 * @property string|null                    $crop_url
 */
class Face extends Model
{
	use HasFactory;
	/** @phpstan-use HasRandomIDAndLegacyTimeBasedID<Face> */
	use HasRandomIDAndLegacyTimeBasedID;
	use ThrowsConsistentExceptions;
	use ToArrayThrowsNotImplemented;

	/**
	 * @var string The type of the primary key
	 */
	protected $keyType = 'string';

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * @var list<string>
	 */
	protected $fillable = [
		'photo_id',
		'person_id',
		'x',
		'y',
		'width',
		'height',
		'confidence',
		'crop_token',
		'is_dismissed',
	];

	/**
	 * @var array<string, string|class-string>
	 */
	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'x' => 'float',
		'y' => 'float',
		'width' => 'float',
		'height' => 'float',
		'confidence' => 'float',
		'is_dismissed' => 'boolean',
	];

	/**
	 * @var list<string>
	 */
	protected $appends = ['crop_url'];

	/**
	 * Get the crop URL derived from the crop_token.
	 * Path format: uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg.
	 *
	 * @return string|null
	 */
	public function getCropUrlAttribute(): ?string
	{
		if ($this->crop_token === null) {
			return null;
		}
		$tok = $this->crop_token;

		return 'uploads/faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg';
	}

	/**
	 * Return the photo this face belongs to.
	 *
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class, 'photo_id', 'id');
	}

	/**
	 * Return the person associated with this face.
	 *
	 * @return BelongsTo<Person,$this>
	 */
	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class, 'person_id', 'id');
	}

	/**
	 * Return the suggestions for this face (pre-computed similar faces).
	 *
	 * @return HasMany<FaceSuggestion,$this>
	 */
	public function suggestions(): HasMany
	{
		return $this->hasMany(FaceSuggestion::class, 'face_id', 'id');
	}
}
