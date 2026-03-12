<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies;

use App\Actions\Search\ColourNameMap;
use App\Contracts\Search\PhotoSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use App\Models\Colour;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Handles `color:` / `colour:` search tokens.
 *
 * Finds photos whose palette contains at least one colour within the
 * configured Manhattan RGB distance of the target colour.
 *
 * Named CSS colours (e.g. "red") are resolved via {@link ColourNameMap::NAMES}.
 * Hex colours (#rrggbb) are parsed directly.
 *
 * SQL EXISTS subquery (valid on SQLite, MySQL, PostgreSQL):
 *   EXISTS (
 *     SELECT 1 FROM palette p
 *     JOIN colours c ON (c.id = p.colour_1 OR c.id = p.colour_2 OR ...
 *                        OR c.id = p.colour_5)
 *     WHERE p.photo_id = photos.id
 *       AND ABS(c.R - :R) + ABS(c.G - :G) + ABS(c.B - :B) <= :dist
 *   )
 *
 * Photos without a palette row are excluded (EXISTS fails naturally).
 */
class ColourStrategy implements PhotoSearchTokenStrategy
{
	public function __construct(private readonly ConfigManager $config_manager)
	{
	}

	public function apply(Builder $query, SearchToken $token): void
	{
		$hex = $this->resolveHex($token->value);
		$colour = Colour::fromHex($hex);
		$dist = $this->config_manager->getValueAsInt('search_colour_distance');

		$r = $colour->R;
		$g = $colour->G;
		$b = $colour->B;

		$query->whereExists(function (\Illuminate\Database\Query\Builder $sub) use ($r, $g, $b, $dist): void {
			$sub->select(\Illuminate\Support\Facades\DB::raw('1'))
				->from('palette as p')
				->join('colours as c', function (\Illuminate\Database\Query\JoinClause $join): void {
					$join->on('c.id', '=', 'p.colour_1')
						->orOn('c.id', '=', 'p.colour_2')
						->orOn('c.id', '=', 'p.colour_3')
						->orOn('c.id', '=', 'p.colour_4')
						->orOn('c.id', '=', 'p.colour_5');
				})
				->whereColumn('p.photo_id', 'photos.id')
				->whereRaw('ABS(c.R - ?) + ABS(c.G - ?) + ABS(c.B - ?) <= ?', [$r, $g, $b, $dist]);
		});
	}

	/**
	 * Resolve a colour input (hex or CSS name) to a lowercase '#rrggbb' string.
	 *
	 * @throws ValidationException when the name is not in {@link ColourNameMap::NAMES}
	 */
	private function resolveHex(string $value): string
	{
		if (str_starts_with($value, '#')) {
			return strtolower($value);
		}

		$name = strtolower($value);
		if (!isset(ColourNameMap::NAMES[$name])) {
			throw ValidationException::withMessages(['term' => "Unknown colour name '{$value}'. Use a #rrggbb hex value or one of: " . implode(', ', array_keys(ColourNameMap::NAMES)) . '.']);
		}

		return ColourNameMap::NAMES[$name];
	}
}
