<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\Actions\Photo\Strategies;

use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use App\DTO\ImportParam;
use App\Enum\StorageDiskType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\StreamStat;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

/**
 * Adds a video as partner to an existing photo.
 *
 * Note the asymmetry to {@link AddPhotoPartnerStrategy}.
 * A video is always added to an already existing photo, and, in particular,
 * all EXIF data are taken from the that photo.
 * This allows to use {@link MediaFile} as the source of the video, because
 * no EXIF data needs to be extracted from the video.
 */
final class AddVideoPartnerStrategy extends AbstractAddStrategy
{
	protected BaseMediaFile $videoSourceFile;

	public function __construct(ImportParam $parameters, BaseMediaFile $video_source_file, Photo $existing_photo)
	{
		parent::__construct($parameters, $existing_photo);
		$this->videoSourceFile = $video_source_file;
	}

	/**
	 * @return Photo
	 *
	 * @throws MediaFileOperationException
	 * @throws ModelDBException
	 * @throws ConfigurationException
	 */
	public function do(): Photo
	{
		$photo_file = $this->photo->size_variants->getOriginal()->getFile();
		$photo_path = $photo_file->getRelativePath();
		$photo_ext = $photo_file->getOriginalExtension();
		$video_ext = $this->videoSourceFile->getOriginalExtension();
		$video_path = substr($photo_path, 0, -strlen($photo_ext)) . $video_ext;
		$video_target_file = new FlysystemFile(Storage::disk(StorageDiskType::LOCAL->value), $video_path);
		$stream_stat = $this->putSourceIntoFinalDestination($video_target_file);
		$this->photo->live_photo_short_path = $video_path;
		$this->photo->live_photo_checksum = $stream_stat?->checksum;
		$this->photo->save();

		return $this->photo;
	}

	/**
	 * Puts the video source file into the final position at video target file.
	 *
	 * We need to distinguish two cases:
	 *
	 * A) The video source file is a native, local file.
	 *
	 * In this case, the video file has just been uploaded (and the photo
	 * partner is already on the target disk).
	 * The video file must be put onto the target disk, the same way as it
	 * would for a stand-alone upload.
	 *
	 * B) The video source file is a FlysystemFile, too.
	 *
	 * In this case, the video file is already on the final disk, but in the
	 * wrong position.
	 * In that case we can and must rename it.
	 * Note, that we must take a little extra care, if the final disk is also
	 * local and the video file has been imported via a symbolic link.
	 * We want to rename the symbolic link, not the target of the symbolic
	 * link.
	 *
	 * @param FlysystemFile $videoTargetFile
	 *
	 * @return StreamStat|null statistics about the uploaded video file; `null` if no file has been uploaded, but renamed in-place
	 *
	 * @throws MediaFileOperationException
	 * @throws ConfigurationException
	 */
	protected function putSourceIntoFinalDestination(FlysystemFile $video_target_file): ?StreamStat
	{
		try {
			if ($this->videoSourceFile instanceof NativeLocalFile) {
				// This is case A (see above)
				// The code is very similar to
				// AddStandaloneStrategy::putSourceIntoFinalDestination()
				// except that we can skip the part about normalization of
				// orientation, because we don't support that for videos.
				if ($this->parameters->importMode->shallImportViaSymlink) {
					if (!$video_target_file->isLocalFile()) {
						throw new ConfigurationException('Symlinking is only supported on local filesystems');
					}
					$target_path = $video_target_file->toLocalFile()->getPath();
					$source_path = $this->videoSourceFile->getRealPath();
					// For symlinks we must manually create a non-existing
					// parent directory.
					// This mimics the behaviour of Flysystem for regular files.
					$target_directory = pathinfo($target_path, PATHINFO_DIRNAME);
					if (!is_dir($target_directory)) {
						$umask = \umask(0);
						\Safe\mkdir($target_directory, BasicPermissionCheck::getConfiguredDirectoryPerm(), true);
						\umask($umask);
					}
					\Safe\symlink($source_path, $target_path);
					$stream_stat = StreamStat::createFromLocalFile($this->videoSourceFile);
				} else {
					$stream_stat = $video_target_file->write($this->videoSourceFile->read(), true);
					$this->videoSourceFile->close();
					$video_target_file->close();
					if ($this->parameters->importMode->shallDeleteImported) {
						// This may throw an exception, if the original has been
						// readable, but is not writable
						// In this case, the media file will have been copied, but
						// cannot be "moved".
						try {
							$this->videoSourceFile->delete();
						} catch (MediaFileOperationException $e) {
							// If deletion failed, we do not cancel the whole
							// import, but fall back to copy-semantics and
							// log the exception
							Handler::reportSafely($e);
						}
					}
				}
			} elseif ($this->videoSourceFile instanceof FlysystemFile) {
				// It seems as if Flysystem calls a primitive \rename under the
				// hood, if the storage adapter is the `Local` adapter.
				// This also works for symbolic links, so we are good here.
				$this->videoSourceFile->move($video_target_file->getRelativePath());
				$stream_stat = null;
			} else {
				throw new LycheeAssertionError('Unexpected type of $videoSourceFile: ' . get_class($this->videoSourceFile));
			}

			return $stream_stat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Could move/copy/symlink source file to final destination', $e);
		}
	}
}
