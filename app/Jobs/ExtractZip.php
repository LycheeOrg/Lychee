<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Album\Create;
use App\Actions\Import\Exec;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Exceptions\Internal\ZipExtractionException;
use App\Image\Files\ProcessableJobFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\JobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Safe\date;
use function Safe\unlink;

class ExtractZip implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	public string $file_path;
	public string $original_base_name;
	public ?string $album_id;
	public int $user_id;
	public ?int $file_last_modified_time;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		ProcessableJobFile $file,
		string|AbstractAlbum|null $album_id,
		?int $file_last_modified_time,
	) {
		$this->file_path = $file->getPath();
		$this->original_base_name = $file->getOriginalBasename();
		$this->album_id = is_string($album_id) ? $album_id : $album_id?->get_id();
		$this->user_id = Auth::user()->id;
		$this->file_last_modified_time = $file_last_modified_time;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->user_id;
		$this->history->job = Str::limit('Extracting: ' . $this->original_base_name, 200);
		$this->history->status = JobStatus::READY;

		$this->history->save();
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$extracted_folder_name = $this->getExtractFolderName();

		$path_extracted = Storage::disk('extract-jobs')->path(date('Ymd') . $extracted_folder_name);
		$zip = new \ZipArchive();
		if ($zip->open($this->file_path) === true) {
			$zip->extractTo($path_extracted);
			$zip->close();

			// clean up the zip file
			unlink($this->file_path);

			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();
		} else {
			throw new ZipExtractionException($this->file_path, $path_extracted);
		}

		$import_mode = new ImportMode(
			delete_imported: true,
			skip_duplicates: false,
			import_via_symlink: false,
			resync_metadata: false,
			shall_rename_photo_title: Configs::getValueAsBool('renamer_photo_title_enabled'),
			shall_rename_album_title: Configs::getValueAsBool('renamer_album_title_enabled'),
		);

		$exec = new Exec(
			import_mode: $import_mode,
			intended_owner_id: $this->user_id,
			delete_missing_photos: false,
			delete_missing_albums: false,
			is_dry_run: false,
		);

		/** @var Album $parent_album */
		$parent_album = $this->album_id !== null ? Album::query()->findOrFail($this->album_id) : null; // in case no ID provided -> import to root folder

		$exec->do($path_extracted, $parent_album);

		CleanUpExtraction::dispatch($path_extracted);
	}

	/**
	 * Catch failures.
	 *
	 * @param \Throwable $th
	 *
	 * @return void
	 */
	public function failed(\Throwable $th): void
	{
		$this->history->status = JobStatus::FAILURE;
		$this->history->save();

		if ($th->getCode() === 999) {
			$this->release();
		} else {
			Log::error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}

	/**
	 * Returns a folder name where:
	 * - spaces are replaced by `_`
	 * - if folder already exists (with date prefix) then we pad with _(xx) where xx is the next available number.
	 *
	 * @return string
	 */
	private function getExtractFolderName(): string
	{
		$orignal_name = substr($this->original_base_name, 0, -4);

		// Iterate on that one.
		$candidate_name = $orignal_name;

		// count
		$i = 0;
		while (Storage::disk('extract-jobs')->exists(date('Ymd') . $candidate_name)) {
			$candidate_name = $orignal_name . ' (' . $i . ')';
			$i++;
		}

		return $candidate_name;
	}
}
