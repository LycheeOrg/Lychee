<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Image\Handlers;

use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\ModelFunctions\H264MOVFormat;
use App\ModelFunctions\MOVFormat;
use App\Repositories\ConfigManager;
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
	private const CODEC_H264 = 'h264';

	/** @var TemporaryLocalFile a working copy of the video stream */
	protected TemporaryLocalFile $working_copy;

	/**
	 * Path at which the video of a motion photo is stored: next to the
	 * original, under the same file name, with the final video extension.
	 *
	 * @param string $original_path path of the original size variant, relative to its disk
	 */
	public static function videoPathNextTo(string $original_path): string
	{
		return pathinfo($original_path, PATHINFO_DIRNAME) . '/' . pathinfo($original_path, PATHINFO_FILENAME) . self::FINAL_VIDEO_FILE_EXTENSION;
	}

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
		$config_manager = resolve(ConfigManager::class);

		if (!$config_manager->hasFFmpeg()) {
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
				'ffmpeg.binaries' => $config_manager->getValueAsString('ffmpeg_path'),
				'ffprobe.binaries' => $config_manager->getValueAsString('ffprobe_path'),
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
	 * An H.264 stream is copied as is; any other codec (HEVC on current
	 * Pixel phones) is transcoded to H.264 so that every browser can play it
	 * (Feature 100, Q-100-09).
	 *
	 * @param NativeLocalFile $file the file to write into
	 *
	 * @throws MediaFileOperationException
	 */
	public function saveVideoStream(NativeLocalFile $file): void
	{
		try {
			$format = $this->isH264() ? new MOVFormat() : new H264MOVFormat();
			// Add additional parameter to extract the first video stream
			$format->setAdditionalParameters([...($format->getAdditionalParameters() ?? []), '-map', '0:0']);
			$this->video->save($format, $file->getRealPath());
			// @codeCoverageIgnoreStart
		} catch (RuntimeException $e) {
			throw new MediaFileOperationException('Could not save video stream from Google Motion Picture', $e);
		} catch (InvalidArgumentException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Whether the first video stream is already H.264.
	 */
	private function isH264(): bool
	{
		return $this->video->getStreams()->videos()->first()?->get('codec_name') === self::CODEC_H264;
	}
}