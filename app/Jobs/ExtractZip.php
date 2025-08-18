<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Album\Create;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\JobStatus;
use App\Enum\SmartAlbumType;
use App\Exceptions\Internal\ZipExtractionException;
use App\Image\Files\ExtractedJobFile;
use App\Image\Files\ProcessableJobFile;
use App\Models\Album;
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

		$new_album = $this->createAlbum($extracted_folder_name, $this->album_id);
		$jobs = [];
		foreach (new \DirectoryIterator($path_extracted) as $file_info) {
			if ($file_info->isDot() || $file_info->isDir()) {
				continue;
			}

			$extracted_file = new ExtractedJobFile($file_info->getRealPath(), $file_info->getFilename());
			$jobs[] = new ProcessImageJob($extracted_file, $new_album, $file_info->getMTime());
		}

		$jobs[] = new CleanUpExtraction($path_extracted);
		foreach ($jobs as $job) {
			dispatch($job);
		}
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
	 * Given a name and parent we create it.
	 *
	 * @param string      $new_album_name
	 * @param string|null $parent_id
	 *
	 * @return Album new album
	 */
	private function createAlbum(string $new_album_name, ?string $parent_id): Album
	{
		if (SmartAlbumType::tryFrom($parent_id) !== null) {
			$parent_id = null;
		}

		/** @var Album $parent_album */
		$parent_album = $parent_id !== null ? Album::query()->findOrFail($parent_id) : null; // in case no ID provided -> import to root folder
		$create_album = new Create($this->user_id);

		return $create_album->create($this->prepareAlbumName($new_album_name), $parent_album);
	}

	/**
	 * Todo Later: add renamer module.
	 *
	 * @param string $album_name_candidate
	 *
	 * @return string
	 */
	private function prepareAlbumName(string $album_name_candidate): string
	{
		return trim(str_replace('_', ' ', $album_name_candidate));
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
		$base_name_without_extension = substr($this->original_base_name, 0, -4);

		// Save that one (is default if no existing folder found).
		$orignal_name = str_replace(' ', '_', $base_name_without_extension);

		// Iterate on that one.
		$candidate_name = $orignal_name;

		// count
		$i = 0;
		while (Storage::disk('extract-jobs')->exists(date('Ymd') . $candidate_name)) {
			$candidate_name = $orignal_name . '_(' . $i . ')';
			$i++;
		}

		return $candidate_name;
	}
}
