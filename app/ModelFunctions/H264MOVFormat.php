<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\ModelFunctions;

use App\Exceptions\Internal\LycheeAssertionError;
use FFMpeg\Exception\InvalidArgumentException;

/**
 * Format class for FFMpeg to transcode a video stream to H.264 in a
 * Quicktime (MOV) container.
 *
 * Current Pixel phones record the clip of a Google Motion Photo in HEVC,
 * which several browsers cannot decode (Feature 100, Q-100-09). H.264 plays
 * everywhere. Quality is set by a constant rate factor rather than a
 * bitrate, so a single pass is enough; FFmpeg's default auto-rotation applies
 * the clip's rotation to the frames.
 */
class H264MOVFormat extends MOVFormat
{
	public const FFMPEG_VIDEO_CODEC_ID = 'libx264';

	/**
	 * No bitrate: quality comes from `-crf` (see {@see self::ENCODER_PARAMETERS}).
	 */
	protected $kiloBitrate = 0;

	/** @var string[] */
	private const ENCODER_PARAMETERS = [
		'-crf', '23',
		'-preset', 'veryfast',
		'-pix_fmt', 'yuv420p',
		'-movflags', '+faststart',
	];

	public function __construct()
	{
		parent::__construct();
		try {
			$this
				->setVideoCodec(self::FFMPEG_VIDEO_CODEC_ID)
				->setAdditionalParameters(self::ENCODER_PARAMETERS);
			// @codeCoverageIgnoreStart
		} catch (InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * The parent's `copy` stays available: its constructor sets it first.
	 *
	 * @return string[]
	 */
	public function getAvailableVideoCodecs(): array
	{
		return [...parent::getAvailableVideoCodecs(), self::FFMPEG_VIDEO_CODEC_ID];
	}
}
