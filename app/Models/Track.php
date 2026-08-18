<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\StorageDiskType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\Track.
 *
 * @property int             $id
 * @property string          $album_id
 * @property Album|null      $album
 * @property string          $name
 * @property string          $file_name
 * @property StorageDiskType $disk
 * @property bool            $is_primary
 * @property string          $url
 *
 * @mixin \Eloquent
 */
class Track extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\TrackFactory> */
	use HasFactory;

	/**
	 * @var array<string,string>
	 */
	protected $casts = [
		'id' => 'integer',
		'disk' => StorageDiskType::class,
		'is_primary' => 'boolean',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = ['album_id', 'name', 'file_name', 'disk', 'is_primary'];

	protected static function booted(): void
	{
		static::creating(function (Track $track): void {
			$track->disk ??= StorageDiskType::LOCAL;
		});
	}

	/**
	 * @return BelongsTo<Album,$this>
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(Album::class, 'album_id', 'id');
	}

	/**
	 * Accessor for the "virtual" attribute {@link Track::$url}.
	 *
	 * @return string the url of the track
	 */
	public function getUrlAttribute(): string
	{
		return Storage::disk($this->disk->value)->url($this->file_name);
	}
}
