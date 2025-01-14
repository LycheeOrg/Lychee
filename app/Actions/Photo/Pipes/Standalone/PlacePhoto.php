<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
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
		// `$this->sourceFile` will be deleted after this step.
		// But `$this->sourceImage` remains in memory.
		$state->targetFile = $state->namingStrategy->createFile(SizeVariantType::ORIGINAL);
		$state->streamStat = $this->putSourceIntoFinalDestination($state);

		return $next($state);
	}

	/**
	 * Moves/copies/symlinks source file to final destination and
	 * normalizes orientation, if necessary.
	 *
	 * Note, {@link AddStandaloneStrategy::$sourceFile} and
	 * {@link AddStandaloneStrategy::$sourceImage} must be set before this
	 * method is called.
	 *
	 * If import via symbolic link is requested, then a symbolic link
	 * from `$targetFile` to {@link AddStandaloneStrategy::$sourceFile} is
	 * created.
	 * Otherwise the content of {@link AddStandaloneStrategy::$sourceFile}
	 * is physically copied/moved into `$targetFile`.
	 *
	 * If the source file requires normalization, then
	 * {@link AddStandaloneStrategy::$sourceImage} is saved to `$targetFile`.
	 * This step implicitly corrects the orientation.
	 * Otherwise, the original byte stream from
	 * {@link AddStandaloneStrategy::$sourceFile} is written to `$targetFile`
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
			if ($state->shallImportViaSymlink) {
				if (!$state->targetFile->isLocalFile()) {
					throw new ConfigurationException('Symlinking is only supported on local filesystems');
				}
				$targetPath = $state->targetFile->toLocalFile()->getPath();
				$sourcePath = $state->sourceFile->getRealPath();
				// For symlinks we must manually create a non-existing
				// parent directory.
				// This mimics the behaviour of Flysystem for regular files.
				$targetDirectory = pathinfo($targetPath, PATHINFO_DIRNAME);
				if (!is_dir($targetDirectory)) {
					$umask = \umask(0);
					\Safe\mkdir($targetDirectory, BasicPermissionCheck::getConfiguredDirectoryPerm(), true);
					\umask($umask);
				}
				\Safe\symlink($sourcePath, $targetPath);
				$streamStat = StreamStat::createFromLocalFile($state->sourceFile);
			} else {
				$shallNormalize = Configs::getValueAsBool('auto_fix_orientation') &&
					$state->sourceImage !== null &&
					$state->exifInfo->orientation !== 1;

				if ($shallNormalize) {
					// Saving the loaded image to the final target normalizes
					// the image orientation. This comes at the cost that
					// the image is re-encoded and hence its quality might
					// be reduced.
					$streamStat = $state->sourceImage->save($state->targetFile, true);
					$this->backupOriginal($state);
				} else {
					// If the image does not require normalization the
					// unaltered source file is copied to the final target.
					// Avoiding a re-encoding prevents any potential quality
					// loss.
					$streamStat = $state->targetFile->write($state->sourceFile->read(), true);
					$state->sourceFile->close();
					$state->targetFile->close();
				}
				if ($state->shallDeleteImported) {
					// This may throw an exception, if the original has been
					// readable, but is not writable
					// In this case, the media file will have been copied, but
					// cannot be "moved".
					try {
						$state->sourceFile->delete();
					} catch (MediaFileOperationException $e) {
						// If deletion failed, we do not cancel the whole
						// import, but fall back to copy-semantics and
						// log the exception
						Handler::reportSafely($e);
					}
				}
			}

			return $streamStat;
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
		$state->backupFile = $state->namingStrategy->createFile(SizeVariantType::ORIGINAL, true);
		$state->backupFile->write($state->sourceFile->read(), true);
	}
}
