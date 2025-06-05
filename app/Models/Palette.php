<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

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
	 * @return array{colour_1:string,colour_2:string,colour_3:string,colour_4:string,colour_5:string}
	 */
	public function toHexColours(): array
	{
		return [
			'colour_1' => self::toHex($this->colour_1),
			'colour_2' => self::toHex($this->colour_2),
			'colour_3' => self::toHex($this->colour_3),
			'colour_4' => self::toHex($this->colour_4),
			'colour_5' => self::toHex($this->colour_5),
		];
	}

	public static function toHex(int $colour)
	{
		$b = $colour & 0xFF; // Extract the blue component
		$g = ($colour >> 8) & 0xFF; // Extract the green component
		$r = ($colour >> 16) & 0xFF; // Extract the red component

		return sprintf('#%02x%02x%02x', $r, $g, $b);
	}
}
