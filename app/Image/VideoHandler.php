<?php

namespace App\Image;

use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\MediaFileOperationException;
use App\Models\Configs;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Exception\ExecutableNotFoundException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

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
	 * (like {@link VideoHandler::saveFrame()}) which assume the file to exist
	 * will fail.
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
			$ffmpeg = FFMpeg::create();
			$this->video = $ffmpeg->open($file->getAbsolutePath());
		} catch (ExecutableNotFoundException $e) {
			throw new ExternalComponentMissingException('FFmpeg not found', $e);
		} catch (InvalidArgumentException $e) {
			throw new MediaFileOperationException('FFmpeg could not open media file', $e);
		}
	}

	/**
	 * Extracts and saves a frame from the video as image.
	 *
	 * @param NativeLocalFile $file          the file to write into
	 * @param float           $framePosition the position of the frame to be extracted in seconds
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	public function saveFrame(NativeLocalFile $file, float $framePosition = 0.0): void
	{
		try {
			$frame = $this->video->frame(TimeCode::fromSeconds($framePosition));
			$frame->save($file->getAbsolutePath());
		} catch (RuntimeException $e) {
			throw new MediaFileOperationException('Could not extract frame from video file', $e);
		}
		if (Configs::get_value('lossless_optimization')) {
			ImageOptimizer::optimize($file->getAbsolutePath());
		}
	}
}
