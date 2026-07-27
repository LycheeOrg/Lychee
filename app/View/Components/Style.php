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
	// default is sky colour palette, but it will be replaced by the accent color palette if set
	public string $ui_50 = 'oklch(97.7% 0.013 236.62)';
	public string $ui_100 = 'oklch(95.1% 0.026 236.824)';
	public string $ui_200 = 'oklch(90.1% 0.058 230.902)';
	public string $ui_300 = 'oklch(82.8% 0.111 230.318)';
	public string $ui_400 = 'oklch(74.6% 0.16 232.661)';
	public string $ui_500 = 'oklch(68.5% 0.169 237.323)';
	public string $ui_600 = 'oklch(58.8% 0.158 241.966)';
	public string $ui_700 = 'oklch(50% 0.134 242.749)';
	public string $ui_800 = 'oklch(44.3% 0.11 240.79)';
	public string $ui_900 = 'oklch(39.1% 0.09 240.876)';
	public string $ui_950 = 'oklch(29.3% 0.066 243.157)';

	public function __construct()
	{
		// default data
		$accent_color = request()->configs()->getValueAsString('accent_color');

		if ($accent_color === '') {
			return;
		}

		$palette = Cache::rememberForever($accent_color, function () use ($accent_color) {
			$palette = PaletteGenerator::generatePalette($accent_color);

			return $palette;
		});

		$this->ui_50 = $palette['50'];
		$this->ui_100 = $palette['100'];
		$this->ui_200 = $palette['200'];
		$this->ui_300 = $palette['300'];
		$this->ui_400 = $palette['400'];
		$this->ui_500 = $palette['500'];
		$this->ui_600 = $palette['600'];
		$this->ui_700 = $palette['700'];
		$this->ui_800 = $palette['800'];
		$this->ui_900 = $palette['900'];
		$this->ui_950 = $palette['950'];
	}

	/**
	 * Render component.
	 */
	public function render(): View
	{
		return view('components.style');
	}
}