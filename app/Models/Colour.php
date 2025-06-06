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

	/**
	 * Create or update a Colour instance from a hexadecimal string.
	 *
	 * @param string $hex
	 *
	 * @return Colour
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function fromHex(string $hex): self
	{
		// Remove the '#' character if it exists
		$hex = ltrim($hex, '#');

		if (strlen($hex) !== 6) {
			throw new \InvalidArgumentException('Hex string must be 6 characters long.');
		}

		$id = hexdec($hex); // Use the hex value as the ID

		return Colour::updateOrCreate([
			'id' => $id,
		],
			[
				'id' => $id,
				'R' => hexdec(substr($hex, 0, 2)),
				'G' => hexdec(substr($hex, 2, 2)),
				'B' => hexdec(substr($hex, 4, 2)),
			]);
	}
}
