<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use App\Contracts\Image\StreamStats;
use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\Handler;
use App\Exceptions\MediaFileOperationException;
use App\Image\StreamStat;
use App\Models\Configs;

class PlacePhoto implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Create target file and symlink/copy/move source file to target.
		// If the import strategy requests to delete the source file
		// `$this->source_file` will be deleted after this step.
		// But `$this->source_image` remains in memory.
		$state->target_file = $state->naming_strategy->createFile(SizeVariantType::ORIGINAL);
		$state->stream_stat = $this->putSourceIntoFinalDestination($state);

		return $next($state);
	}

	/**
	 * Moves/copies/symlinks source file to final destination and
	 * normalizes orientation, if necessary.
	 *
	 * Note, {@link AddStandaloneStrategy::$source_file} and
	 * {@link AddStandaloneStrategy::$source_image} must be set before this
	 * method is called.
	 *
	 * If import via symbolic link is requested, then a symbolic link
	 * from `$target_file` to {@link AddStandaloneStrategy::$source_file} is
	 * created.
	 * Otherwise the content of {@link AddStandaloneStrategy::$source_file}
	 * is physically copied/moved into `$target_file`.
	 *
	 * If the source file requires normalization, then
	 * {@link AddStandaloneStrategy::$source_image} is saved to `$target_file`.
	 * This step implicitly corrects the orientation.
	 * Otherwise, the original byte stream from
	 * {@link AddStandaloneStrategy::$source_file} is written to `$target_file`
	 * without modifications.
	 *
	 * @param StandaloneDTO $state State of iteration
	 *
	 * @return StreamStats statistics about the final file, may differ from
	 *                     the source file due to normalization of orientation
	 *
	 * @throws MediaFileOperationException
	 * @throws ConfigurationException
	 */
	private function putSourceIntoFinalDestination(StandaloneDTO $state): StreamStats
	{
		try {
			if ($state->shall_import_via_symlink) {
				if (!$state->target_file->isLocalFile()) {
					throw new ConfigurationException('Symlinking is only supported on local filesystems');
				}
				$target_path = $state->target_file->toLocalFile()->getPath();
				$source_path = $state->source_file->getRealPath();
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
				$stream_stat = StreamStat::createFromLocalFile($state->source_file);
			} else {
				$shall_normalize = Configs::getValueAsBool('auto_fix_orientation') &&
					$state->source_image !== null &&
					$state->exif_info->orientation !== 1;

				if ($shall_normalize) {
					// Saving the loaded image to the final target normalizes
					// the image orientation. This comes at the cost that
					// the image is re-encoded and hence its quality might
					// be reduced.
					$stream_stat = $state->source_image->save($state->target_file, true);
					$this->backupOriginal($state);
				} else {
					// If the image does not require normalization the
					// unaltered source file is copied to the final target.
					// Avoiding a re-encoding prevents any potential quality
					// loss.
					$stream_stat = $state->target_file->write($state->source_file->read(), true);
					$state->source_file->close();
					$state->target_file->close();
				}
				if ($state->shall_delete_imported) {
					// This may throw an exception, if the original has been
					// readable, but is not writable
					// In this case, the media file will have been copied, but
					// cannot be "moved".
					try {
						$state->source_file->delete();
					} catch (MediaFileOperationException $e) {
						// If deletion failed, we do not cancel the whole
						// import, but fall back to copy-semantics and
						// log the exception
						Handler::reportSafely($e);
					}
				}
			}

			return $stream_stat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Could move/copy/symlink source file to final destination', $e);
		}
	}

	/**
	 * When rotating, we backup the original file to prevent data loss.
	 *
	 * @param StandaloneDTO $state
	 *
	 * @return void
	 */
	private function backupOriginal(StandaloneDTO $state)
	{
		$state->backup_file = $state->naming_strategy->createFile(SizeVariantType::ORIGINAL, true);
		$state->backup_file->write($state->source_file->read(), true);
	}
}
