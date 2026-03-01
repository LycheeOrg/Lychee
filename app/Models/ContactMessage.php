<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ContactMessage.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $message
 * @property bool        $is_read
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class ContactMessage extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\ContactMessageFactory> */
	use HasFactory;
	use ThrowsConsistentExceptions;

	/**
	 * @var list<string>
	 */
	protected $fillable = [
		'name',
		'email',
		'message',
		'is_read',
		'ip_address',
		'user_agent',
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'is_read' => 'boolean',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	/**
	 * @var list<string>
	 */
	protected $hidden = [
		'ip_address',
		'user_agent',
	];
}
