<?php

namespace App\Actions\Photo\Strategies;

use App\Metadata\Extractor;
use App\Models\Album;
use App\Models\Configs;

class AddStrategyParameters
{
	public ImportMode $importMode;

	/** @var Album|null the intended parent album */
	public ?Album $album = null;

	/** @var bool indicates whether the new photo shall be public */
	public bool $is_public = false;

	/** @var bool indicates whether the new photo shall be starred */
	public bool $is_starred = false;

	/** @var Extractor|null the extracted EXIF information */
	public ?Extractor $exifInfo = null;

	public function __construct(?ImportMode $importMode = null)
	{
		$this->importMode = $importMode ?: new ImportMode(false,
			Configs::get_value('skip_duplicates', '0') === '1'
		);
	}
}
