<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\StorageDiskType;
use App\Exceptions\Internal\FileDeletionException;
use App\Exceptions\MediaFileOperationException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use function Safe\unlink;

/**
 * Deletes a bounded list of files from one storage disk.
 *
 * Producers build these jobs with {@link FileDeleterJob::batches()}, so that
 * no job carries more than {@link FileDeleterJob::BATCH_SIZE} paths.
 */
class FileDeleterJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	/**
	 * Maximum number of paths carried by one job (FR-112-03).
	 */
	public const BATCH_SIZE = 500;

	/**
	 * Create a new job instance.
	 *
	 * @param StorageDiskType   $storage_type
	 * @param array<int,string> $file_list
	 */
	public function __construct(
		public StorageDiskType $storage_type,
		public array $file_list,
	) {
	}

	/**
	 * Split the paths of one disk into jobs of at most {@link self::BATCH_SIZE} paths.
	 * An empty list produces no job.
	 *
	 * @param StorageDiskType   $storage_type
	 * @param array<int,string> $file_list
	 *
	 * @return list<FileDeleterJob>
	 */
	public static function batches(StorageDiskType $storage_type, array $file_list): array
	{
		return array_map(
			fn (array $chunk) => new self($storage_type, $chunk),
			array_chunk(array_values($file_list), self::BATCH_SIZE)
		);
	}

	/**
	 * Execute the job.
	 *
	 * Deletes every file of the list. Missing files are skipped silently.
	 * Errors are collected and the first one is rethrown after the loop,
	 * so one failing file does not stop the others.
	 */
	public function handle(): void
	{
		if ($this->file_list === []) {
			Log::channel('jobs')->debug('No files to delete.');

			return;
		}

		$disk = Storage::disk($this->storage_type->value);

		// If the disk uses the local driver, we use low-level routines as
		// these are also able to handle symbolic links in case of doubt
		$first_exception = $disk->getAdapter() instanceof LocalFilesystemAdapter
			? $this->deleteLocal($disk)
			: $this->deleteRemote($disk);

		Log::channel('jobs')->debug(sprintf('Processed %d paths on %s.', count($this->file_list), $this->storage_type->value));

		if ($first_exception !== null) {
			throw new MediaFileOperationException('Could not delete some files', $first_exception);
		}
	}

	/**
	 * Delete the files of a local disk.
	 *
	 * @return \Throwable|null the first error, if any
	 */
	private function deleteLocal(Filesystem $disk): ?\Throwable
	{
		$first_exception = null;
		foreach ($this->file_list as $file) {
			try {
				$absolute_path = $disk->path($file);
				// Note, `file_exist` returns `false` for existing,
				// but dead links.
				// So the first part takes care of deleting links no matter
				// if they are dead or alive.
				// The latter part deletes (regular) files, but avoids errors
				// in case the file doesn't exist.
				if (is_link($absolute_path) || file_exists($absolute_path)) {
					unlink($absolute_path);
				}
			} catch (\Throwable $e) {
				$first_exception = $first_exception ?? $e;
			}
		}

		return $first_exception;
	}

	/**
	 * Delete the files of a remote disk (S3).
	 *
	 * Each file is a regular object. There is no `exists()` pre-check:
	 * deleting a missing key is a no-op success, so one call per file is enough.
	 *
	 * @return \Throwable|null the first error, if any
	 */
	private function deleteRemote(Filesystem $disk): ?\Throwable
	{
		$first_exception = null;
		foreach ($this->file_list as $file) {
			try {
				if (!$disk->delete($file)) {
					$first_exception = $first_exception ?? new FileDeletionException($this->storage_type->value, $file);
				}
			} catch (\Throwable $e) {
				$first_exception = $first_exception ?? $e;
			}
		}

		return $first_exception;
	}
}
