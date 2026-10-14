<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only moderation history of a photo.
 *
 * Rows are written by {@link \App\Services\ModerationHistory} and never updated.
 *
 * @property int                        $id
 * @property string                     $photo_id
 * @property ModerationEvent            $event
 * @property ModerationSource           $source
 * @property int|null                   $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property Photo                      $photo
 * @property User|null                  $user
 */
class PhotoModerationEvent extends Model
{
	public const UPDATED_AT = null;

	protected $table = 'photo_moderation_events';

	protected $fillable = [
		'photo_id',
		'event',
		'source',
		'user_id',
		'created_at',
	];

	protected $casts = [
		'event' => ModerationEvent::class,
		'source' => ModerationSource::class,
		'user_id' => 'integer',
		'created_at' => 'datetime',
	];

	/**
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class, 'photo_id', 'id');
	}

	/**
	 * @return BelongsTo<User,$this>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
