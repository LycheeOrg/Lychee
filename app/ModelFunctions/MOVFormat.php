<?php

namespace App\ModelFunctions;

use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Format\Video\DefaultVideo;

/**
 * Format class for FFmpeg to store a single video stream in a Quicktime (MOV) container.
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
		} catch (InvalidArgumentException $e) {
			assert(false, new \AssertionError('copy codec must always be supported', $e));
		}
	}

	public function supportBFrames(): bool
	{
		return false;
	}

	/**
	 * Returns the extra parameters to be added to the FFmpeg command line.
	 *
	 * Here we force FFmpeg to use the Quicktime Container format for output.
	 *
	 * TODO: Why do we enforce Apple Quicktime as the output container? This is suspicious.
	 *
	 * Note, that we only use this format to store extracted video streams
	 * from Google Motions Pictures.
	 * A Google Motion Picture uses AVC (H.264) as the codec in an MP4
	 * (MPEG-4 Part 1) container.
	 * We do want to re-pack it in another container?
	 *
	 * @return string[]
	 */
	public function getExtraParams(): array
	{
		return ['-f', self::FFMPEG_CONTAINER_ID];
	}

	public function getAvailableAudioCodecs(): array
	{
		return [self::FFMPEG_AUDIO_CODEC_ID];
	}

	public function getAvailableVideoCodecs(): array
	{
		return [self::FFMPEG_VIDEO_CODEC_ID];
	}
}
