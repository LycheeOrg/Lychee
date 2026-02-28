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
 * App\Models\AlbumSizeStatistics.
 *
 * Pre-computed size statistics for albums by size variant type.
 * Eliminates expensive runtime aggregations across size_variants table.
 *
 * @property string $album_id      Primary key, foreign key to albums.id
 * @property int    $size_raw      Total bytes for RAW variants
 * @property int    $size_thumb    Total bytes for THUMB variants
 * @property int    $size_thumb2x  Total bytes for THUMB2X variants
 * @property int    $size_small    Total bytes for SMALL variants
 * @property int    $size_small2x  Total bytes for SMALL2X variants
 * @property int    $size_medium   Total bytes for MEDIUM variants
 * @property int    $size_medium2x Total bytes for MEDIUM2X variants
 * @property int    $size_original Total bytes for ORIGINAL variants
 * @property Album  $album         The album these statistics belong to
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumSizeStatistics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumSizeStatistics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumSizeStatistics query()
 * @method static \Illuminate\Database\Eloquent\Builder|AlbumSizeStatistics whereAlbumId($value)
 *
 * @mixin \Eloquent
 */
class AlbumSizeStatistics extends Model
{
	use ThrowsConsistentExceptions;
	use HasFactory;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'album_size_statistics';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'album_id';

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * The "type" of the primary key ID.
	 *
	 * @var string
	 */
	protected $keyType = 'string';

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
		'album_id',
		'size_raw',
		'size_thumb',
		'size_thumb2x',
		'size_small',
		'size_small2x',
		'size_medium',
		'size_medium2x',
		'size_original',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'size_raw' => 'integer',
		'size_thumb' => 'integer',
		'size_thumb2x' => 'integer',
		'size_small' => 'integer',
		'size_small2x' => 'integer',
		'size_medium' => 'integer',
		'size_medium2x' => 'integer',
		'size_original' => 'integer',
	];

	/**
	 * Get the album that owns these statistics.
	 *
	 * @return BelongsTo<Album,$this>
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(Album::class, 'album_id', 'id');
	}
}
