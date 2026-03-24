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
 * App\Models\Person.
 *
 * @property string               $id
 * @property string               $name
 * @property int|null             $user_id
 * @property bool                 $is_searchable
 * @property string|null          $representative_face_id
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
	 * @param \Illuminate\Database\Eloquent\Builder<static> $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder<static>
	 */
	public function scopeSearchable($query)
	{
		return $query->where('is_searchable', '=', true);
	}
}
