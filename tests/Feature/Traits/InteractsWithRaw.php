<?php

namespace Tests\Feature\Traits;

use App\Image\Files\BaseMediaFile;
use App\Models\Configs;
use Tests\Feature\Constants\TestConstants;

trait InteractsWithRaw
{
	public static function getAcceptedRawFormats(): string
	{
		return Configs::getValueAsString(TestConstants::CONFIG_RAW_FORMATS);
	}

	public static function setAcceptedRawFormats(string $acceptedRawFormats): void
	{
		Configs::set(TestConstants::CONFIG_RAW_FORMATS, $acceptedRawFormats);
		$reflection = new \ReflectionClass(BaseMediaFile::class);
		$reflection->setStaticPropertyValue('cachedAcceptedRawFileExtensions', []);
	}
}
