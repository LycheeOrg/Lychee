<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\View\Components;

use CharlieEtienne\PaletteGenerator\PaletteGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * This is the bottom of the page.
 * We provides socials etc...
 */
class Style extends Component
{
	/**
	 * Maps a design token to the config key that customizes it. Any token
	 * left at its default ('') is omitted from $palettes entirely, so
	 * app-v8.css's static ramp for that token applies unchanged - no
	 * hardcoded fallback values to keep in sync here.
	 */
	private const TOKEN_KEYS = [
		'primary' => 'primary_color',
		'secondary' => 'secondary_color',
		'success' => 'success_color',
		'warning' => 'warning_color',
		'error' => 'error_color',
		'info' => 'info_color',
		'neutral' => 'neutral_color',
	];

	/** @var array<string,array<string,string>> token => (shade => oklch value) */
	public array $palettes = [];

	public function __construct()
	{
		$configs = request()->configs();

		foreach (self::TOKEN_KEYS as $token => $key) {
			$color = $configs->getValueAsString($key);

			if ($color === '') {
				continue;
			}

			$this->palettes[$token] = Cache::rememberForever($color, function () use ($color) {
				return PaletteGenerator::generatePalette($color);
			});
		}
	}

	/**
	 * Render component.
	 */
	public function render(): View
	{
		return view('components.style');
	}
}