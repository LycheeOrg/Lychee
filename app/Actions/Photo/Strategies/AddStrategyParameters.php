<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Models\Album;
use App\Models\Configs;

class AddStrategyParameters
{
	public ImportMode $importMode;

	// Information about intended parent album
	public ?Album $album = null;
	public bool $public = false;
	public bool $star = false;

	// Information about source file
	public string $kind = '';
	public ?SourceFileInfo $sourceFileInfo = null;
	public array $info = [];

	public function __construct(?ImportMode $importMode = null)
	{
		$this->importMode = $importMode ?: new ImportMode(false,
			Configs::get_value('skip_duplicates', '0') === '1'
		);
	}
}
