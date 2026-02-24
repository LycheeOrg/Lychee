<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Photo\Create;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\Enum\JobStatus;
use App\Exceptions\OwnerRequiredException;
use App\Factories\AlbumFactory;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\TemporaryJobFile;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\Repositories\ConfigManager;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * This allows to process images on serverside while making the responses faster.
 * Note that the user will NOT see that the image is processed directly in upload when using queues.
 */
class ProcessImageJob implements ShouldQueue
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
	public ?bool $apply_watermark;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		ProcessableJobFile $file,
		string|AbstractAlbum|null $abstract_album,
		?int $file_last_modified_time,
		?bool $apply_watermark = null,
	) {
		$this->file_path = $file->getPath();
		$this->original_base_name = $file->getOriginalBasename();

		$this->album_id = null;

		$album = null;

		if (is_string($abstract_album)) {
			$album = resolve(AlbumFactory::class)->findAbstractAlbumOrFail($abstract_album);
		} elseif ($abstract_album instanceof AbstractAlbum) {
			$album = $abstract_album;
		}

		$this->album_id = $album?->get_id();
		$album_name = $album?->get_title() ?? __('gallery.smart_album.unsorted');
		$user_id = Auth::user()?->id;
		if ($user_id === null && ($album === null || $album instanceof BaseSmartAlbum)) {
			throw new OwnerRequiredException();
		}
		/** @var Album|TagAlbum $album */
		$this->user_id = $user_id ?? $album?->owner_id ?? throw new OwnerRequiredException();

		$this->file_last_modified_time = $file_last_modified_time;

		// Enforce watermark_optout_disabled restriction
		// If admin has disabled opt-out, ignore user's preference and use global setting
		if (resolve(ConfigManager::class)->getValueAsBool('watermark_optout_disabled')) {
			$this->apply_watermark = null;
		} else {
			$this->apply_watermark = $apply_watermark;
		}

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->user_id;
		$this->history->job = Str::limit(sprintf('Process Image: %s added to %s.', $this->original_base_name, $album_name), 200);
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
		Log::channel('jobs')->info($this->history->job);

		$copied_file = new TemporaryJobFile($this->file_path, $this->original_base_name);

		$config_manager = app(ConfigManager::class);

		// As the file has been uploaded, the (temporary) source file shall be deleted
		$create = new Create(
			import_mode: new ImportMode(
				delete_imported: true,
				skip_duplicates: $config_manager->getValueAsBool('skip_duplicates'),
				shall_rename_photo_title: $config_manager->getValueAsBool('renamer_photo_title_enabled'),
			),
			intended_owner_id: $this->user_id
		);

		$album = null;
		if ($this->album_id !== null) {
			$album = $album_factory->findAbstractAlbumOrFail($this->album_id);
		}

		$photo = $create->add($copied_file, $album, $this->file_last_modified_time);

		// Once the job has finished, set history status to 1.
		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();

		return $photo;
	}
}