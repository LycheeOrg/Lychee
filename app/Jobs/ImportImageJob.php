<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Photo\Create;
use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Factories\AlbumFactory;
use App\Image\Files\NativeLocalFile;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
	public string $album_id;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		NativeLocalFile $file,
		public int $intended_owner_id,
		public ImportMode $import_mode,
		public Album $album,
	) {
		$this->file_path = $file->getPath();
		$this->album_id = $album->id;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->intended_owner_id;
		$this->history->job = Str::limit(sprintf('Process Image: %s added to %s.', basename($this->file_path), $album->title), 200);
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
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$copied_file = new NativeLocalFile($this->file_path);

		// As the file has been uploaded, the (temporary) source file shall be
		// deleted
		$create = new Create(
			$this->import_mode,
			$this->intended_owner_id,
		);

		$album = $album_factory->findAbstractAlbumOrFail($this->album_id);
		$photo = $create->add($copied_file, $album);

		// Once the job has finished, set history status to 1.
		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();

		return $photo;
	}
}
