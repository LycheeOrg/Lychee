<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\PhotoLayoutType;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoLayoutConfig extends Data
{
	public PhotoLayoutType $photos_layout;
	public int $photo_layout_justified_row_height;
	public int $photo_layout_masonry_column_width;
	public int $photo_layout_grid_column_width;
	public int $photo_layout_square_column_width;
	public int $photo_layout_gap;

	public function __construct()
	{
		$this->photo_layout_justified_row_height = Configs::getValueAsInt('photo_layout_justified_row_height');
		$this->photo_layout_masonry_column_width = Configs::getValueAsInt('photo_layout_masonry_column_width');
		$this->photo_layout_grid_column_width = Configs::getValueAsInt('photo_layout_grid_column_width');
		$this->photo_layout_square_column_width = Configs::getValueAsInt('photo_layout_square_column_width');
		$this->photo_layout_gap = Configs::getValueAsInt('photo_layout_gap');
		$this->photos_layout = Configs::getValueAsEnum('layout', PhotoLayoutType::class);
	}
}
