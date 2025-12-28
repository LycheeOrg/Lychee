<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PhotoRating.
 *
 * @property int    $id
 * @property string $photo_id
 * @property int    $user_id
 * @property int    $rating
 * @property Photo  $photo
 * @property User   $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoRating wherePhotoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoRating whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhotoRating whereRating($value)
 *
 * @mixin \Eloquent
 */
class PhotoRating extends Model
{
	use ThrowsConsistentExceptions;
	use HasFactory;

	public $timestamps = false;

	protected $fillable = [
		'photo_id',
		'user_id',
		'rating',
	];

	protected $casts = [
		'rating' => 'integer',
		'user_id' => 'integer',
	];

	/**
	 * Get the photo that this rating belongs to.
	 *
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class, 'photo_id', 'id');
	}

	/**
	 * Get the user who created this rating.
	 *
	 * @return BelongsTo<User,$this>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
