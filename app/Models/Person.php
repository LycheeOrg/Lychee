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
 * App\Models\Person.
 *
 * @property string               $id
 * @property string               $name
 * @property int|null             $user_id
 * @property bool                 $is_searchable
 * @property string|null          $representative_face_id
 * @property int                  $face_count
 * @property int                  $photo_count
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property User|null            $user
 * @property Collection<int,Face> $faces
 * @property Face|null            $representativeFace
 */
class Person extends Model
{
	use HasFactory;
	/** @phpstan-use HasRandomIDAndLegacyTimeBasedID<Person> */
	use HasRandomIDAndLegacyTimeBasedID;
	use ThrowsConsistentExceptions;
	use ToArrayThrowsNotImplemented;

	protected $table = 'persons';

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
		'name',
		'user_id',
		'is_searchable',
		'representative_face_id',
		'face_count',
		'photo_count',
	];

	/**
	 * @return array<string,string>
	 */
	protected function casts(): array
	{
		return [
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
			'is_searchable' => 'boolean',
			'user_id' => 'integer',
			'representative_face_id' => 'string',
			'face_count' => 'integer',
			'photo_count' => 'integer',
		];
	}

	/**
	 * Return the user linked to this person.
	 *
	 * @return BelongsTo<User,$this>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	/**
	 * Return the faces associated with this person.
	 *
	 * @return HasMany<Face,$this>
	 */
	public function faces(): HasMany
	{
		return $this->hasMany(Face::class, 'person_id', 'id');
	}

	/**
	 * Return the representative face for this person (nullable).
	 *
	 * @return BelongsTo<Face,$this>
	 */
	public function representativeFace(): BelongsTo
	{
		return $this->belongsTo(Face::class, 'representative_face_id', 'id');
	}

	/**
	 * Scope to only include persons visible to a given user.
	 * Always includes searchable persons; if $user_id is provided, also includes
	 * the person linked to that user.
	 *
	 * @param Builder<static> $query
	 * @param int|null        $user_id
	 *
	 * @return Builder<static>
	 */
	public function scopeSearchable(
		Builder $query,
		?int $user_id = null,
	): Builder {
		return $query->where(function (Builder $q) use ($user_id): void {
			$q->where('persons.is_searchable', '=', true);
			if ($user_id !== null) {
				$q->orWhere('persons.user_id', '=', $user_id);
			}
		});
	}
}
