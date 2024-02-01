<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';
	public const POSITIVE = 'positive';
	public const BOOL = '0|1';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_layout_justified_row_height',
				'value' => '320',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => self::POSITIVE,
				'description' => 'Heights of rows in Justified photo layout',
			],
			[
				// md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7
				'key' => 'photo_layout_masonry_column_width',
				'value' => '300',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => self::POSITIVE,
				'description' => 'Minimum column width in Masonry photo layout.',
			],
			[
				// md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7
				'key' => 'photo_layout_grid_column_width',
				'value' => '250',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => self::POSITIVE,
				'description' => 'Minimum column width in Grid photo layout.',
			],
			[
				// md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7
				'key' => 'photo_layout_square_column_width',
				'value' => '200',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => self::POSITIVE,
				'description' => 'Minimum column width in Square photo layout.',
			],
			[
				'key' => 'photo_layout_gap',
				'value' => '12',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => self::POSITIVE,
				'description' => 'Gap between columns in Square/Masonry/Grid photo layout.',
			],
		];
	}
};
