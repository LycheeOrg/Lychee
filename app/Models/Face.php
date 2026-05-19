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
use Illuminate\Database\Eloquent\Builder;
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
 * @property float                          $laplacian_variance
 * @property string|null                    $crop_token
 * @property bool                           $is_dismissed
 * @property int|null                       $cluster_label
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 * @property Photo                          $photo
 * @property Person|null                    $person
 * @property Collection<int,FaceSuggestion> $suggestions
 * @property string|null                    $crop_url
 *
 * @method static \Illuminate\Database\Eloquent\Builder<Face> notDismissed()
 * @method static \Illuminate\Database\Eloquent\Builder<Face> dismissed()
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
		'laplacian_variance',
		'crop_token',
		'is_dismissed',
		'cluster_label',
	];

	/**
	 * @var list<string>
	 */
	protected $appends = [
		'crop_url',
	];

	/**
	 * @return array<string,string>
	 */
	protected function casts(): array
	{
		return [
			'x' => 'float',
			'y' => 'float',
			'width' => 'float',
			'height' => 'float',
			'confidence' => 'float',
			'laplacian_variance' => 'float',
			'is_dismissed' => 'boolean',
			'cluster_label' => 'integer',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];
	}

	/**
	 * Compute the crop URL from the crop_token.
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

	/**
	 * Scope: only faces that have not been dismissed.
	 *
	 * @param Builder<Face> $query
	 *
	 * @return Builder<Face>
	 */
	public function scopeNotDismissed(Builder $query): Builder
	{
		return $query->where('is_dismissed', '=', false);
	}

	/**
	 * Scope: only faces that have been dismissed.
	 *
	 * @param Builder<Face> $query
	 *
	 * @return Builder<Face>
	 */
	public function scopeDismissed(Builder $query): Builder
	{
		return $query->where('is_dismissed', '=', true);
	}
}
