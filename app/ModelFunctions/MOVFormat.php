<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\ModelFunctions;

use App\Exceptions\Internal\LycheeAssertionError;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Format\Video\DefaultVideo;

/**
 * Format class for FFMpeg to store a single video stream in a Quicktime (MOV) container.
 */
class MOVFormat extends DefaultVideo
{
	public const FFMPEG_VIDEO_CODEC_ID = 'copy';
	public const FFMPEG_AUDIO_CODEC_ID = 'copy';
	public const FFMPEG_CONTAINER_ID = 'mov';

	public function __construct()
	{
		try {
			$this
				->setAudioCodec(self::FFMPEG_AUDIO_CODEC_ID)
				->setVideoCodec(self::FFMPEG_VIDEO_CODEC_ID);
			// @codeCoverageIgnoreStart
		} catch (InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function supportBFrames(): bool
	{
		return false;
	}

	/**
	 * Returns the extra parameters to be added to the FFMpeg command line.
	 *
	 * Here we force FFMpeg to use the Quicktime Container format for output.
	 * Natively, a Google Motion Picture uses the video codec AVC (H.264)
	 * in an MP4 (MPEG-4 Part 1) container.
	 * But the JS package `livephotoskit/livephotoskit` which handles
	 * live photos on the frontend only supports Quicktime containers
	 * (at least this was the case in 2019, see
	 * [comment in issue #378](https://github.com/LycheeOrg/Lychee/issues/378#issuecomment-548687276)
	 * and the [related pull request #172](https://github.com/LycheeOrg/Lychee-front/pull/172)).
	 * Hence, we re-packetize the video stream into a Quicktime container.
	 *
	 * @return string[]
	 */
	public function getExtraParams(): array
	{
		return ['-f', self::FFMPEG_CONTAINER_ID];
	}

	/**
	 * @return string[]
	 */
	public function getAvailableAudioCodecs(): array
	{
		return [self::FFMPEG_AUDIO_CODEC_ID];
	}

	/**
	 * @return string[]
	 */
	public function getAvailableVideoCodecs(): array
	{
		return [self::FFMPEG_VIDEO_CODEC_ID];
	}
}
