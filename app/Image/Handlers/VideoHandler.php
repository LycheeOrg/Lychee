<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Handlers;

use App\Contracts\Image\ImageHandlerInterface;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Models\Configs;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Exception\ExecutableNotFoundException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;

class VideoHandler
{
	protected ?Video $video = null;

	/**
	 * "Loads" a video from the provided input file.
	 *
	 * **Warning:** Opposed to images, the video is not entirely loaded into
	 * memory.
	 * Hence, the physical on-disk representation of provided input file must
	 * exist at least as long as the object of this class is used.
	 * Otherwise, subsequent calls to method
	 * (like {@link VideoHandler::extractFrame()}) which assume the file to
	 * exist will fail.
	 *
	 * @throws ExternalComponentMissingException
	 * @throws ConfigurationException
	 * @throws MediaFileOperationException
	 */
	public function load(NativeLocalFile $file): void
	{
		if (!Configs::hasFFmpeg()) {
			throw new ConfigurationException('FFmpeg is disabled by configuration');
		}
		try {
			$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries' => Configs::getValueAsString('ffmpeg_path'),
				'ffprobe.binaries' => Configs::getValueAsString('ffprobe_path'),
			]);
			$audio_or_video = $ffmpeg->open($file->getRealPath());
			if ($audio_or_video instanceof Video) {
				$this->video = $audio_or_video;
			} else {
				throw new MediaFileOperationException('No video streams found.');
			}
		} catch (ExecutableNotFoundException $e) {
			throw new ExternalComponentMissingException('FFmpeg not found', $e);
		} catch (InvalidArgumentException $e) {
			throw new MediaFileOperationException('FFmpeg could not open media file', $e);
		}
	}

	/**
	 * Extracts and returns a frame from the video.
	 *
	 * @throws MediaFileOperationException
	 * @throws ImageProcessingException
	 * @throws MediaFileUnsupportedException
	 */
	public function extractFrame(float $frame_position = 0.0): ImageHandlerInterface
	{
		try {
			// A temporary, local file for the extracted frame
			$frame_file = new TemporaryLocalFile('.jpg');
			$frame = $this->video->frame(TimeCode::fromSeconds($frame_position));
			$frame->save($frame_file->getRealPath());

			// Load the extracted frame into the image handler
			$frame = new ImageHandler();
			$frame->load($frame_file);
			$frame_file->delete();

			return $frame;
		} catch (RuntimeException $e) {
			throw new MediaFileOperationException('Could not extract frame from video file', $e);
		}
	}
}