<?php

declare(strict_types=1);

namespace App\Livewire\DTO;

use App\Enum\PhotoLayoutType;
use App\Models\Configs;

class Layouts
{
	public function __construct(
		public ?string $photos_layout = null,
		public ?int $photo_layout_justified_row_height = null,
		public ?int $photo_layout_masonry_column_width = null,
		public ?int $photo_layout_grid_column_width = null,
		public ?int $photo_layout_square_column_width = null,
		public ?int $photo_layout_gap = null,
	) {
		$this->photo_layout_justified_row_height ??= Configs::getValueAsInt('photo_layout_justified_row_height');
		$this->photo_layout_masonry_column_width ??= Configs::getValueAsInt('photo_layout_masonry_column_width');
		$this->photo_layout_grid_column_width ??= Configs::getValueAsInt('photo_layout_grid_column_width');
		$this->photo_layout_square_column_width ??= Configs::getValueAsInt('photo_layout_square_column_width');
		$this->photo_layout_gap ??= Configs::getValueAsInt('photo_layout_gap');
		$this->photos_layout ??= Configs::getValueAsEnum('layout', PhotoLayoutType::class)->value;
	}

	public function photos_layout(): PhotoLayoutType
	{
		return PhotoLayoutType::from($this->photos_layout);
	}
}
