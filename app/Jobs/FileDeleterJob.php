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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use function Safe\unlink;

/**
 * This allows to process images on serverside while making the responses faster.
 * Note that the user will NOT see that the image is processed directly in upload when using queues.
 */
class FileDeleterJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

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
	 * Execute the job.
	 *
	 * Here we handle the execution of the image processing.
	 * This will create the model, reformat the image etc.
	 */
	public function handle(): void
	{
		$first_exception = null;

		$disk = Storage::disk($this->storage_type->value);

		// If the disk uses the local driver, we use low-level routines as
		// these are also able to handle symbolic links in case of doubt
		$is_local_disk = $disk->getAdapter() instanceof LocalFilesystemAdapter;
		if ($is_local_disk) {
			foreach ($this->file_list as $file) {
				Log::channel('jobs')->debug("Delete {$file}.");
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
		} else {
			// @codeCoverageIgnoreStart
			// If the disk is not local, we can assume that each file is a regular file
			foreach ($this->file_list as $file) {
				Log::channel('jobs')->debug("Delete {$file}.");
				try {
					if ($disk->exists($file)) {
						if (!$disk->delete($file)) {
							$first_exception = $first_exception ?? new FileDeletionException($this->storage_type->value, $file);
						}
					}
				} catch (\Throwable $e) {
					$first_exception = $first_exception ?? $e;
				}
			}
			// @codeCoverageIgnoreEnd
		}

		if ($first_exception !== null) {
			throw new MediaFileOperationException('Could not delete some files', $first_exception);
		}
	}
}
