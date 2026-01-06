<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Palette.
 *
 * @property int    $id
 * @property string $photo_id
 * @property int    $colour_1
 * @property int    $colour_2
 * @property int    $colour_3
 * @property int    $colour_4
 * @property int    $colour_5
 */
class Palette extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\PaletteFactory> */
	use HasFactory;

	public $timestamps = false;

	protected $fillable = [
		'photo_id',
		'colour_1',
		'colour_2',
		'colour_3',
		'colour_4',
		'colour_5',
	];

	/**
	 * Convert a colour integer to a hex string.
	 *
	 * @param int $colour The colour in integer format (0xRRGGBB)
	 *
	 * @return string The hex representation of the colour
	 */
	public static function toHex(int $colour)
	{
		$b = $colour & 0xFF; // Extract the blue component
		$g = ($colour >> 8) & 0xFF; // Extract the green component
		$r = ($colour >> 16) & 0xFF; // Extract the red component

		return sprintf('#%02x%02x%02x', $r, $g, $b);
	}
}
