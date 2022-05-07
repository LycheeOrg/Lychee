<?php

namespace App\Image;

use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\MediaFileOperationException;
use App\ModelFunctions\MOVFormat;
use App\Models\Configs;
use FFMpeg\Exception\ExecutableNotFoundException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\FFMpeg;

/**
 * Class GoogleMotionPictureHandler.
 *
 * This class provides methods to extract the additional video stream inside
 * Google Motion Pictures and save the stream to an independent file.
 *
 * A Google Motion Picture (GMP) puts the video stream into the file after the
 * image binary.
 * Hence, image libraries or programs which simply read the image don't care
 * because they stop reading from the file as seen as the image is complete.
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
	protected TemporaryLocalFile $workingCopy;

	/**
	 * @throws MediaFileOperationException
	 */
	public function __destruct()
	{
		$this->workingCopy->delete();
	}

	/**
	 * Loads a video stream from a Google Motion picture.
	 *
	 * @param NativeLocalFile $file        the Google Motion Picture
	 * @param int             $videoLength the length of the video stream in bytes from the end of the file; `0` indicates the video stream fills the whole file
	 *
	 * @return void
	 *
	 * @throws ExternalComponentMissingException
	 * @throws ConfigurationException
	 * @throws InvalidConfigOption
	 * @throws MediaFileOperationException
	 */
	public function load(NativeLocalFile $file, int $videoLength = 0): void
	{
		if (!Configs::hasFFmpeg()) {
			throw new ConfigurationException('FFmpeg is disabled by configuration');
		}

		try {
			$this->workingCopy = new TemporaryLocalFile(self::PRELIMINARY_VIDEO_FILE_EXTENSION, $file->getBasename());
			$readStream = $file->read();
			if ($videoLength !== 0) {
				fseek($readStream, -$videoLength, SEEK_END);
			}
			$this->workingCopy->write($readStream);
			$file->close();

			$ffmpeg = FFMpeg::create();
			$this->video = $ffmpeg->open($this->workingCopy->getAbsolutePath());
		} catch (ExecutableNotFoundException $e) {
			throw new ExternalComponentMissingException('FFmpeg not found', $e);
		} catch (InvalidArgumentException $e) {
			throw new MediaFileOperationException('FFmpeg could not open media file', $e);
		}
	}

	/**
	 * Save the video stream to the provided file.
	 *
	 * @param NativeLocalFile $file the file to write into
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	public function saveVideoStream(NativeLocalFile $file): void
	{
		try {
			$format = new MOVFormat();
			// Add additional parameter to extract the first video stream
			$format->setAdditionalParameters(['-map', '0:0']);
			$this->video->save($format, $file->getAbsolutePath());
		} catch (RuntimeException $e) {
			throw new MediaFileOperationException('Could not save video stream from Google Motion Picture', $e);
		} catch (InvalidArgumentException $e) {
			assert(false, new \AssertionError('setAdditionalParameters failed', $e));
		}
	}
}
