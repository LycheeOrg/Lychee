<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Handlers;

use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\ModelFunctions\MOVFormat;
use App\Models\Configs;
use FFMpeg\Exception\ExecutableNotFoundException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;

/**
 * Class GoogleMotionPictureHandler.
 *
 * This class provides methods to extract the additional video stream inside
 * Google Motion Pictures and save the stream to an independent file.
 *
 * A Google Motion Picture (GMP) puts the video stream into the file after the
 * image binary.
 * Hence, image libraries or programs which simply read the image don't care
 * because they stop reading from the file as soon as the image is complete.
 * The start of the video stream from the end of the file is encoded in the
 * EXIF attribute `MicroVideoOffset`.
 *
 * See here: https://medium.com/android-news/working-with-motion-photos-da0aa49b50c
 */
class GoogleMotionPictureHandler extends VideoHandler
{
	public const PRELIMINARY_VIDEO_FILE_EXTENSION = '.mp4';
	public const FINAL_VIDEO_FILE_EXTENSION = '.mov';

	/** @var TemporaryLocalFile a working copy of the video stream */
	protected TemporaryLocalFile $working_copy;

	/**
	 * @throws MediaFileOperationException
	 */
	public function __destruct()
	{
		if (isset($this->working_copy)) {
			$this->working_copy->delete();
		}
	}

	/**
	 * Loads a video stream from a Google Motion picture.
	 *
	 * @param NativeLocalFile $file         the Google Motion Picture
	 * @param int             $video_length the length of the video stream in bytes from the end of the file; `0` indicates the video stream fills the whole file
	 *
	 * @throws ExternalComponentMissingException
	 * @throws ConfigurationException
	 * @throws InvalidConfigOption
	 * @throws MediaFileOperationException
	 */
	public function load(NativeLocalFile $file, int $video_length = 0): void
	{
		if (!Configs::hasFFmpeg()) {
			throw new ConfigurationException('FFmpeg is disabled by configuration');
		}

		try {
			$this->working_copy = new TemporaryLocalFile(self::PRELIMINARY_VIDEO_FILE_EXTENSION, $file->getBasename());
			$read_stream = $file->read();
			if ($video_length !== 0) {
				fseek($read_stream, -$video_length, SEEK_END);
			}
			$this->working_copy->write($read_stream);
			$file->close();

			$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries' => Configs::getValueAsString('ffmpeg_path'),
				'ffprobe.binaries' => Configs::getValueAsString('ffprobe_path'),
			]);
			$audio_or_video = $ffmpeg->open($this->working_copy->getRealPath());
			if ($audio_or_video instanceof Video) {
				$this->video = $audio_or_video;
			} else {
				throw new MediaFileOperationException('No video stream found');
			}
		} catch (ExecutableNotFoundException $e) {
			throw new ExternalComponentMissingException('FFmpeg not found', $e);
		} catch (InvalidArgumentException $e) {
			throw new MediaFileOperationException('FFmpeg could not open media file', $e);
		} catch (RuntimeException $e) {
			throw new MediaFileOperationException('Could not load video stream from Google Motion Picture', $e);
		}
	}

	/**
	 * Save the video stream to the provided file.
	 *
	 * @param NativeLocalFile $file the file to write into
	 *
	 * @throws MediaFileOperationException
	 */
	public function saveVideoStream(NativeLocalFile $file): void
	{
		try {
			$format = new MOVFormat();
			// Add additional parameter to extract the first video stream
			$format->setAdditionalParameters(['-map', '0:0']);
			$this->video->save($format, $file->getRealPath());
			// @codeCoverageIgnoreStart
		} catch (RuntimeException $e) {
			throw new MediaFileOperationException('Could not save video stream from Google Motion Picture', $e);
		} catch (InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}