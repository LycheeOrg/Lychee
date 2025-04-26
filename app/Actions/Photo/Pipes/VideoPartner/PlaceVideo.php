<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\VideoPartner;

use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use App\Assets\Features;
use App\Contracts\PhotoCreate\VideoPartnerPipe;
use App\DTO\PhotoCreate\VideoPartnerDTO;
use App\Enum\StorageDiskType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\StreamStat;
use Illuminate\Support\Facades\Storage;

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
class PlaceVideo implements VideoPartnerPipe
{
	public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO
	{
		$disk = Storage::disk(StorageDiskType::LOCAL->value);
		if (Features::active('use-s3')) {
			$disk = Storage::disk(StorageDiskType::S3->value);
		}
		$video_target_file = new FlysystemFile($disk, $state->video_path);

		try {
			if ($state->video_file instanceof NativeLocalFile) {
				// This is case A (see above)
				// The code is very similar to
				// AddStandaloneStrategy::putSourceIntoFinalDestination()
				// except that we can skip the part about normalization of
				// orientation, because we don't support that for videos.
				if ($state->shall_import_via_symlink) {
					if (!$video_target_file->isLocalFile()) {
						throw new ConfigurationException('Symlinking is only supported on local filesystems');
					}
					$target_path = $video_target_file->toLocalFile()->getPath();
					$source_path = $state->video_file->getRealPath();
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
					$stream_stat = StreamStat::createFromLocalFile($state->video_file);
				} else {
					$stream_stat = $video_target_file->write($state->video_file->read(), true);
					$state->video_file->close();
					$video_target_file->close();
					if ($state->shall_delete_imported) {
						// This may throw an exception, if the original has been
						// readable, but is not writable
						// In this case, the media file will have been copied, but
						// cannot be "moved".
						try {
							$state->video_file->delete();
						} catch (MediaFileOperationException $e) {
							// If deletion failed, we do not cancel the whole
							// import, but fall back to copy-semantics and
							// log the exception
							Handler::reportSafely($e);
						}
					}
				}
			} elseif ($state->video_file instanceof FlysystemFile) {
				// It seems as if Flysystem calls a primitive \rename under the
				// hood, if the storage adapter is the `Local` adapter.
				// This also works for symbolic links, so we are good here.
				$state->video_file->move($video_target_file->getRelativePath());
				$stream_stat = null;
			} else {
				throw new LycheeAssertionError('Unexpected type of $video_file: ' . get_class($state->video_file));
			}

			$state->stream_stat = $stream_stat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Could move/copy/symlink source file to final destination', $e);
		}

		return $next($state);
	}
}
