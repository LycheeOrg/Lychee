<?php

namespace Tests\Feature\Traits;

use App\Image\MediaFile;
use App\Models\Configs;
use Tests\TestCase;

trait InteractsWithRaw
{
	public static function getAcceptedRawFormats(): string
	{
		return Configs::getValueAsString(TestCase::CONFIG_RAW_FORMATS);
	}

	public static function setAcceptedRawFormats(string $acceptedRawFormats): void
	{
		Configs::set(TestCase::CONFIG_RAW_FORMATS, $acceptedRawFormats);
		$reflection = new \ReflectionClass(MediaFile::class);
		$reflection->setStaticPropertyValue('cachedAcceptedRawFileExtensions', []);
	}
}
