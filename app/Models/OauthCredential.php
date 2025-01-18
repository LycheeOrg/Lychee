<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Enum\OauthProvidersType;
use App\Models\Builders\OauthCredentialBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OauthCredential.
 *
 * This class contains the Oauth token used to identify a user.
 *
 * @property int                $id
 * @property int                $user_id
 * @property User               $user
 * @property OauthProvidersType $provider
 * @property string             $token_id
 */
class OauthCredential extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	protected $fillable = [
		'provider',
		'token_id',
		'user_id',
	];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'user_id' => 'integer',
		'provider' => OauthProvidersType::class,
	];

	protected $hidden = [
		'token_id',
	];

	/**
	 * Return the relationship between a Photo and its Album.
	 *
	 * @return BelongsTo<User,$this>
	 *
	 * @codeCoverageIgnore Tested locally.
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	/**
	 * @param $query
	 *
	 * @return OauthCredentialBuilder
	 */
	public function newEloquentBuilder($query): OauthCredentialBuilder
	{
		return new OauthCredentialBuilder($query);
	}
}
