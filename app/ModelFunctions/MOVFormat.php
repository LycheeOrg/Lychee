<?php

namespace App\ModelFunctions;

use FFMpeg;

// Class for FFMpeg to convert files to mov format
class MOVFormat extends FFMpeg\Format\Video\DefaultVideo
{
	/**
	 * @throws FFMpeg\Exception\InvalidArgumentException
	 */
	public function __construct($audioCodec = 'copy', $videoCodec = 'copy')
	{
		$this
			->setAudioCodec($audioCodec)
			->setVideoCodec($videoCodec);
	}

	public function supportBFrames(): bool
	{
		return false;
	}

	public function getExtraParams(): array
	{
		return ['-f', 'mov'];
	}

	public function getAvailableAudioCodecs(): array
	{
		return ['copy'];
	}

	public function getAvailableVideoCodecs(): array
	{
		return ['copy'];
	}
}
