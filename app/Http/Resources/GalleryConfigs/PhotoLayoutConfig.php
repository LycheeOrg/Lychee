<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoLayoutConfig extends Data
{
	public int $photo_layout_justified_row_height;
	public int $photo_layout_masonry_column_width;
	public int $photo_layout_grid_column_width;
	public int $photo_layout_square_column_width;
	public int $photo_layout_gap;

	public function __construct()
	{
		$this->photo_layout_justified_row_height = request()->configs()->getValueAsInt('photo_layout_justified_row_height');
		$this->photo_layout_masonry_column_width = request()->configs()->getValueAsInt('photo_layout_masonry_column_width');
		$this->photo_layout_grid_column_width = request()->configs()->getValueAsInt('photo_layout_grid_column_width');
		$this->photo_layout_square_column_width = request()->configs()->getValueAsInt('photo_layout_square_column_width');
		$this->photo_layout_gap = request()->configs()->getValueAsInt('photo_layout_gap');
	}
}
