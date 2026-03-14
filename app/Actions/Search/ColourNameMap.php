<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search;

/**
 * Maps the 16 CSS Level 1 colour names (plus "grey" alias) to lowercase
 * 6-digit hex strings.  Used by {@link \App\Actions\Search\Strategies\ColourStrategy}
 * to resolve named colour inputs without any DB access.
 */
final class ColourNameMap
{
	/**
	 * @var array<string,string> lowercase name → '#rrggbb'
	 */
	public const NAMES = [
		'aqua' => '#00ffff',
		'black' => '#000000',
		'blue' => '#0000ff',
		'fuchsia' => '#ff00ff',
		'gray' => '#808080',
		'grey' => '#808080',
		'green' => '#008000',
		'lime' => '#00ff00',
		'maroon' => '#800000',
		'navy' => '#000080',
		'olive' => '#808000',
		'orange' => '#ffa500',
		'purple' => '#800080',
		'red' => '#ff0000',
		'silver' => '#c0c0c0',
		'teal' => '#008080',
		'white' => '#ffffff',
		'yellow' => '#ffff00',
	];
}
