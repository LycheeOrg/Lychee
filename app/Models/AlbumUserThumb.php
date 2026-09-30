<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\AlbumUserThumb.
 *
 * Pre-computed thumb (cover photo) for a smart album, tag album, or person album,
 * cached per viewer. `user_id` is null for the public/guest view of the album.
 * Regular albums store their automatic covers here too, as `is_precomputed`
 * rows keyed by viewer class (Feature 076, ADR-076-01): the owner's row holds
 * the max-privilege cover, the `NULL` (or single shared user's) row the
 * least-privilege one.
 * `album_id` holds either a base_albums.id (regular/tag/person albums) or a SmartAlbumType
 * enum value (e.g. 'recent') - smart albums have no associated DB row, so there is
 * no foreign key on this column, mirroring access_permissions.base_album_id.
 *
 * @property int         $id
 * @property int|null    $user_id        Null means the public/guest view of the album
 * @property string      $album_id       base_albums.id or a SmartAlbumType value
 * @property string      $photo_id
 * @property string|null $photo_id_2     Rank-2 cached cover (Feature 075), null when fewer photos qualify
 * @property string|null $photo_id_3     Rank-3 cached cover (Feature 075), null when fewer photos qualify
 * @property bool        $is_precomputed True for a regular album's automatic cover row written by {@see \App\Jobs\RecomputeAlbumStatsJob} (Feature 076, ADR-076-01); false for a lazily cached tag/person/smart album cover
 * @property User|null   $user
 * @property Photo       $photo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumUserThumb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumUserThumb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumUserThumb query()
 *
 * @mixin \Eloquent
 */
class AlbumUserThumb extends Model
{
	use ThrowsConsistentExceptions;
	use HasFactory;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'album_user_thumbs';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'user_id',
		'album_id',
		'photo_id',
		'photo_id_2',
		'photo_id_3',
		'is_precomputed',
	];

	/**
	 * @var array<string,string>
	 */
	protected $casts = [
		'user_id' => 'integer',
		'is_precomputed' => 'boolean',
	];

	/**
	 * @return BelongsTo<User,$this>
	 */
	/**
	 * The current viewer's cache rows for `$album_ids`, keyed by `album_id`
	 * — one batched query for a whole listing (Feature 075, FR-075-09).
	 * `Auth::id()` is `null` for a guest, matching the cache's convention.
	 *
	 * @param array<int,string> $album_ids
	 *
	 * @return Collection<string,object{album_id:string,photo_id:string,photo_id_2:?string,photo_id_3:?string}>
	 */
	public static function rowsForViewer(array $album_ids): Collection
	{
		if (count($album_ids) === 0) {
			return new Collection();
		}

		/** @var Collection<string,object{album_id:string,photo_id:string,photo_id_2:?string,photo_id_3:?string}> $rows */
		$rows = self::query()
			->toBase()
			->select(['album_id', 'photo_id', 'photo_id_2', 'photo_id_3'])
			->whereIn('album_id', $album_ids)
			->where('user_id', Auth::id())
			->get()
			->keyBy('album_id');

		return $rows;
	}

	/**
	 * Hand the precomputed owner rows of `$album_ids` to `$new_owner_id`
	 * (Feature 076, FR-076-09). Must run *before* `base_albums.owner_id`
	 * changes: an owner row is recognised by `user_id = owner_id`.
	 *
	 * A row already keyed on the new owner of an album they do not own yet
	 * is a single-share row; it is dropped, since the owner row serves them
	 * from now on (invariant I2).
	 *
	 * @param array<int,string> $album_ids
	 */
	public static function rekeyOwnerRows(array $album_ids, int $new_owner_id): void
	{
		foreach (array_chunk($album_ids, 500) as $chunk) {
			self::query()
				->whereIn('album_id', $chunk)
				->where('is_precomputed', '=', true)
				->where('user_id', '=', $new_owner_id)
				->whereExists(fn ($q) => $q->from('base_albums')
					->whereColumn('base_albums.id', '=', 'album_user_thumbs.album_id')
					->where('base_albums.owner_id', '<>', $new_owner_id))
				->delete();

			self::query()
				->whereIn('album_id', $chunk)
				->where('is_precomputed', '=', true)
				->whereExists(fn ($q) => $q->from('base_albums')
					->whereColumn('base_albums.id', '=', 'album_user_thumbs.album_id')
					->whereColumn('base_albums.owner_id', '=', 'album_user_thumbs.user_id'))
				->update(['user_id' => $new_owner_id]);
		}
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	/**
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class, 'photo_id', 'id');
	}
}
