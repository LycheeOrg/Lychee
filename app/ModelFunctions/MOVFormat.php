<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use FFMpeg;

// Class for FFMpeg to convert files to mov format
class MOVFormat extends FFMpeg\Format\Video\DefaultVideo
{
	public function __construct($audioCodec = 'copy', $videoCodec = 'copy')
	{
		$this
			->setAudioCodec($audioCodec)
			->setVideoCodec($videoCodec);
	}

	public function supportBFrames()
	{
		return false;
	}

	public function getExtraParams()
	{
		return ['-f', 'mov'];
	}

	public function getAvailableAudioCodecs()
	{
		return ['copy'];
	}

	public function getAvailableVideoCodecs()
	{
		return ['copy'];
	}
}
