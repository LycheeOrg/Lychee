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
 * `album_id` holds either a base_albums.id (tag/person albums) or a SmartAlbumType
 * enum value (e.g. 'recent') - smart albums have no associated DB row, so there is
 * no foreign key on this column, mirroring access_permissions.base_album_id.
 *
 * @property int         $id
 * @property int|null    $user_id    Null means the public/guest view of the album
 * @property string      $album_id   base_albums.id or a SmartAlbumType value
 * @property string      $photo_id
 * @property string|null $photo_id_2 Rank-2 cached cover (Feature 075), null when fewer photos qualify
 * @property string|null $photo_id_3 Rank-3 cached cover (Feature 075), null when fewer photos qualify
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
