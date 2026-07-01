<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Constants\PersonAlbumPersons;
use App\Exceptions\InvalidPropertyException;
use App\ModelFunctions\HasAbstractAlbumProperties;
use App\Models\Builders\PersonAlbumBuilder;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\Thumb;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Relations\HasManyPhotosByPerson;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\PersonAlbum.
 *
 * @property Collection<int,Person> $persons
 * @property bool                   $is_and
 *
 * @method static PersonAlbumBuilder|PersonAlbum query()                       Begin querying the model.
 * @method static PersonAlbumBuilder|PersonAlbum with(array|string $relations) Begin querying the model with eager loading.
 *
 * @property string                            $id
 * @property BaseAlbumImpl                     $base_class
 * @property User                              $owner
 * @property Collection<int, User>             $shared_with
 * @property int|null                          $shared_with_count
 * @property Collection<int, AccessPermission> $access_permissions
 * @property int|null                          $access_permissions_count
 * @property AccessPermission|null             $current_user_permissions
 * @property AccessPermission|null             $public_permissions
 *
 * @method static PersonAlbumBuilder|PersonAlbum addSelect($column)
 * @method static PersonAlbumBuilder|PersonAlbum join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static PersonAlbumBuilder|PersonAlbum joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static PersonAlbumBuilder|PersonAlbum leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static PersonAlbumBuilder|PersonAlbum newModelQuery()
 * @method static PersonAlbumBuilder|PersonAlbum newQuery()
 * @method static PersonAlbumBuilder|PersonAlbum orderBy($column, $direction = 'asc')
 * @method static PersonAlbumBuilder|PersonAlbum select($columns = [])
 * @method static PersonAlbumBuilder|PersonAlbum whereId($value)
 * @method static PersonAlbumBuilder|PersonAlbum whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static PersonAlbumBuilder|PersonAlbum whereNotIn(string $column, string $values, string $boolean = 'and')
 *
 * @mixin \Eloquent
 */
class PersonAlbum extends BaseAlbum
{
	use ToArrayThrowsNotImplemented;
	use HasFactory;
	use HasAbstractAlbumProperties;

	/**
	 * @var array<string, mixed>
	 */
	protected $attributes = [
		'id' => null,
		'is_and' => null,
	];

	/**
	 * @return array<string,string>
	 */
	protected function casts(): array
	{
		return [
			'is_and' => 'boolean',
			'min_taken_at' => 'datetime',
			'max_taken_at' => 'datetime',
		];
	}

	/**
	 * @var list<string>
	 */
	protected $hidden = [
		'base_class',
	];

	/**
	 * @var list<string>
	 */
	protected $appends = [
		'thumb',
	];

	/**
	 * @phpstan-ignore method.childReturnType, method.childReturnType
	 */
	public function photos(): HasManyPhotosByPerson
	{
		return new HasManyPhotosByPerson($this);
	}

	/**
	 * @return Thumb|null
	 *
	 * @throws InvalidPropertyException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		return Thumb::createFromQueryable(
			$this->photos(),
			$this->getEffectivePhotoSorting(),
		);
	}

	/**
	 * @param BaseBuilder $query
	 *
	 * @return PersonAlbumBuilder
	 */
	public function newEloquentBuilder($query): PersonAlbumBuilder
	{
		return new PersonAlbumBuilder($query);
	}

	/**
	 * All persons attached to this album, regardless of visibility.
	 * Used internally (e.g. to resolve the album's photos via {@link HasManyPhotosByPerson}) and for syncing.
	 * Use {@link PersonAlbum::visiblePersons()} to display persons to the current user.
	 *
	 * @return BelongsToMany<Person,$this>
	 */
	public function persons(): BelongsToMany
	{
		return $this->belongsToMany(
			Person::class,
			PersonAlbumPersons::PERSON_ALBUM_PERSONS,
			'album_id',
			'person_id',
		);
	}

	/**
	 * Persons attached to this album that are visible to the current user,
	 * i.e. searchable persons, plus the person linked to the current user, if any.
	 *
	 * @return BelongsToMany<Person,$this>
	 */
	public function visiblePersons(): BelongsToMany
	{
		return $this->persons()->searchable(Auth::id());
	}
}
