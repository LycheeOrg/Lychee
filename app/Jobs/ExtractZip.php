<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Import\Exec;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Exceptions\Internal\ZipExtractionException;
use App\Exceptions\ZipInvalidException;
use App\Image\Files\BaseMediaFile;
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
	use HasFailedTrait;
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

		$this->validate_zip();

		$path_extracted = Storage::disk('extract-jobs')->path(date('Ymd') . ' ' . $this->getExtractFolderName());
		$this->extract_zip($path_extracted);

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
			should_execute_jobs: false,
		);

		/** @var Album $parent_album */
		$parent_album = $this->album_id !== null ? Album::query()->findOrFail($this->album_id) : null; // in case no ID provided -> import to root folder

		/** @var ImportImageJob[] $jobs */
		$jobs = [];
		if ($this->should_import_from_extracted($path_extracted)) {
			foreach (new \DirectoryIterator($path_extracted) as $file_info) {
				// We only import directories here. Files are imported by the Importer when parsing the directories.
				if ($file_info->isDir() && !$file_info->isDot()) {
					$jobs = array_merge($jobs, $exec->do($file_info->getRealPath(), $parent_album));
				}
			}
		} else {
			$jobs = $exec->do($path_extracted, $parent_album);
		}
		// We have collected all the jobs, now we dispatch them.
		foreach ($jobs as $job) {
			try {
				dispatch($job);
				// @codeCoverageIgnoreStart
			} catch (\Throwable $e) {
				// Fail silently if dispatched sync.
				Log::error(__LINE__ . ':' . __FILE__ . ' ' . $e->getMessage(), $e->getTrace());
			}
			// @codeCoverageIgnoreEnd
		}

		CleanUpExtraction::dispatch($path_extracted, $this->user_id);
	}

	// Option 1: there are folders in the zip file extracted folder -> we import each folder with exec into parent album (we skip the extracted folder)
	// Option 2: there are pictures in the zip file extracted folder -> we import extracted folder with exec (will create a new album)
	private function should_import_from_extracted(string $path_extracted): bool
	{
		foreach (new \DirectoryIterator($path_extracted) as $file_info) {
			if ($file_info->isDot() || $file_info->isDir()) {
				continue;
			}

			// Check if this is an image file
			$extension = strtolower($file_info->getExtension());
			if (BaseMediaFile::isSupportedOrAcceptedFileExtension('.' . $extension)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates a ZIP file for potential security issues like zip slip attacks.
	 *
	 * This method scans the ZIP archive for entries that could be potentially dangerous,
	 * such as paths starting with '/' (absolute paths) or containing '../' (directory traversal).
	 * If any unsafe entries are found, the job is marked as failed, the issue is logged as critical,
	 * and a ZipExtractionException is thrown.
	 *
	 * @return void
	 *
	 * @throws ZipExtractionException If the ZIP file cannot be opened or contains unsafe entries
	 */
	private function validate_zip(): void
	{
		$unsafe_entries = [];

		$zip = new \ZipArchive();
		if ($zip->open($this->file_path) === true) {
			// Filter out suspicious entries to prevent Zip Slip.
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$name = $zip->getNameIndex($i);
				if ($name === false) {
					// @codeCoverageIgnoreStart
					continue;
					// @codeCoverageIgnoreEnd
				}
				// normalize to forward slashes as per ZIP spec
				$entry = str_replace('\\', '/', $name);
				if (str_starts_with($entry, '/') || str_contains($entry, '../')) {
					$unsafe_entries[] = $name;
				}
			}
			$zip->close();

			if (count($unsafe_entries) > 0) {
				Log::critical('Zip file ' . $this->file_path . ' contains unsafe entries.', $unsafe_entries);

				$this->history->status = JobStatus::FAILURE;
				$this->history->save();

				throw new ZipInvalidException($this->file_path . ' contains unsafe entries.');
			}

			return;
		}
		// @codeCoverageIgnoreStart
		throw new ZipExtractionException('Could not open ' . $this->file_path);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Extracts the contents of a ZIP file to the specified path.
	 *
	 * This method opens the ZIP archive and extracts all its contents to the target directory.
	 * After successful extraction, it deletes the original ZIP file and updates the job status.
	 * If extraction fails, a ZipExtractionException is thrown.
	 *
	 * @param string $path_extracted The absolute path where the ZIP contents should be extracted
	 *
	 * @return void
	 *
	 * @throws ZipExtractionException If the ZIP file cannot be opened or extraction fails
	 */
	private function extract_zip(string $path_extracted): void
	{
		$zip = new \ZipArchive();
		if ($zip->open($this->file_path) === true) {
			$zip->extractTo($path_extracted);
			$zip->close();

			// clean up the zip file
			unlink($this->file_path);

			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return;
		}
		// @codeCoverageIgnoreStart
		throw ZipExtractionException::fromTo($this->file_path, $path_extracted);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns a folder name where if folder already exists (with date prefix)
	 * then we pad with ` (xx)` where xx is the next available number.
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
		while (Storage::disk('extract-jobs')->exists(date('Ymd') . ' ' . $candidate_name)) {
			// @codeCoverageIgnoreStart
			$candidate_name = $orignal_name . ' (' . $i . ')';
			$i++;
			// @codeCoverageIgnoreEnd
		}

		return $candidate_name;
	}
}
