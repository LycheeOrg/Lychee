<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Import\ImportPathGuard;
use App\Actions\Photo\Create;
use App\DTO\ImportMode;
use App\Enum\ImportPathStatus;
use App\Enum\JobStatus;
use App\Enum\UserUploadTrustLevel;
use App\Exceptions\ImportFileOutsideRootException;
use App\Factories\AlbumFactory;
use App\Image\Files\NativeLocalFile;
use App\Jobs\Traits\HasFailedTrait;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * This allows to process images on serverside while making the responses faster.
 * Note that the user will NOT see that the image is processed directly in upload when using queues.
 */
class ImportImageJob implements ShouldQueue
{
	use HasFailedTrait;
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;
	public string $file_path;
	public ?string $album_id;

	/**
	 * Create a new job instance.
	 *
	 * @param NativeLocalFile      $file               file to import
	 * @param int                  $intended_owner_id  owner of the photo
	 * @param ImportMode           $import_mode        import options
	 * @param Album|null           $album              target album, `null` for Unsorted
	 * @param UserUploadTrustLevel $upload_trust_level trust level of the owner (FR-115-08)
	 * @param string|null          $import_root        real import root the file must stay in, `null` when unrestricted (FR-115-18)
	 */
	public function __construct(
		NativeLocalFile $file,
		public int $intended_owner_id,
		public ImportMode $import_mode,
		?Album $album,
		public UserUploadTrustLevel $upload_trust_level,
		public ?string $import_root = null,
	) {
		$this->file_path = $file->getPath();
		$this->album_id = $album?->id;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->intended_owner_id;
		$this->history->job = Str::limit(sprintf('Process Image: %s added to %s.', basename($this->file_path), $album?->title ?? 'root'), 200);
		$this->history->status = JobStatus::READY;
		$this->history->save();
	}

	/**
	 * Execute the job.
	 *
	 * Here we handle the execution of the image processing.
	 * This will create the model, reformat the image etc.
	 */
	public function handle(AlbumFactory $album_factory): Photo
	{
		Log::channel('jobs')->info($this->history->job);
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$this->assertStillInsideImportRoot();

		$copied_file = new NativeLocalFile($this->file_path);

		// As the file has been uploaded, the (temporary) source file shall be deleted
		$create = new Create(
			import_mode: $this->import_mode,
			intended_owner_id: $this->intended_owner_id,
			upload_trust_level: $this->upload_trust_level,
		);

		$album = $this->album_id !== null ? $album_factory->findAbstractAlbumOrFail($this->album_id, false) : null;
		$photo = $create->add($copied_file, $album);

		// Once the job has finished, set history status to 1.
		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();

		return $photo;
	}

	/**
	 * The file may have been replaced since the scan (e.g. by a link to a file outside the import root).
	 * Check its real path again before reading it (FR-115-18, TE-115-05).
	 *
	 * @throws ImportFileOutsideRootException
	 */
	private function assertStillInsideImportRoot(): void
	{
		if (ImportPathGuard::check($this->file_path, $this->import_root) === ImportPathStatus::INSIDE) {
			return;
		}

		Log::warning('import file left the import root', ['owner_id' => $this->intended_owner_id, 'path' => $this->file_path]);
		$this->history->status = JobStatus::FAILURE;
		$this->history->save();

		throw new ImportFileOutsideRootException($this->file_path);
	}
}
