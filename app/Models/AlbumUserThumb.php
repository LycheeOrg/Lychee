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

/**
 * App\Models\AlbumUserThumb.
 *
 * Pre-computed thumb (cover photo) for a smart album, tag album, or person album,
 * cached per viewer. `user_id` is null for the public/guest view of the album.
 * `album_id` holds either a base_albums.id (tag/person albums) or a SmartAlbumType
 * enum value (e.g. 'recent') - smart albums have no associated DB row, so there is
 * no foreign key on this column, mirroring access_permissions.base_album_id.
 *
 * @property int       $id
 * @property int|null  $user_id  Null means the public/guest view of the album
 * @property string    $album_id base_albums.id or a SmartAlbumType value
 * @property string    $photo_id
 * @property User|null $user
 * @property Photo     $photo
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
	];

	/**
	 * @return BelongsTo<User,$this>
	 */
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
