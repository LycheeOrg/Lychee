<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Palette;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ColourPaletteResource extends Data
{
	public string $colour_1;
	public string $colour_2;
	public string $colour_3;
	public string $colour_4;
	public string $colour_5;

	public function __construct(Palette $palette)
	{
		$this->colour_1 = Palette::toHex($palette->colour_1);
		$this->colour_2 = Palette::toHex($palette->colour_2);
		$this->colour_3 = Palette::toHex($palette->colour_3);
		$this->colour_4 = Palette::toHex($palette->colour_4);
		$this->colour_5 = Palette::toHex($palette->colour_5);
	}

	public static function fromModel(?Palette $p): ?ColourPaletteResource
	{
		if ($p === null) {
			return null;
		}

		return new self($p);
	}
}
