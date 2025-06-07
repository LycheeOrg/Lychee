<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Colour.
 *
 * @property int $id
 * @property int $R
 * @property int $G
 * @property int $B
 */
class Colour extends Model
{
	public $timestamps = false;

	protected $fillable = [
		'id',
		'R',
		'G',
		'B',
	];

	/**
	 * Convert the colour to a hexadecimal string.
	 *
	 * @return string
	 */
	public function toHex(): string
	{
		return sprintf('#%02x%02x%02x', $this->R, $this->G, $this->B);
	}
}
