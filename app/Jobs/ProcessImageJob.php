<?php

namespace App\Jobs;

use App\Actions\Photo\Create;
use App\Actions\Photo\Strategies\ImportMode;
use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\TemporaryJobFile;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ProcessImageJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public string $filePath;
	public string $originalBaseName;
	public ?string $albumId;
	public int $userId;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		ProcessableJobFile $file,
		?AbstractAlbum $albumId,
	) {
		$this->filePath = $file->getPath();
		$this->originalBaseName = $file->getOriginalBasename();
		$this->albumId = $albumId?->id;
		$this->userId = Auth::user()->id;
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		Auth::loginUsingId($this->userId);

		$copiedFile = new TemporaryJobFile($this->filePath, $this->originalBaseName);

		// As the file has been uploaded, the (temporary) source file shall be
		// deleted
		$create = new Create(new ImportMode(
			true,
			Configs::getValueAsBool('skip_duplicates')
		));
		$albumFactory = resolve(AlbumFactory::class);
		$album = null;

		if ($this->albumId !== null) {
			$album = $albumFactory->findAbstractAlbumOrFail($this->albumId);
		}

		$create->add($copiedFile, $album);

		Auth::logout();
	}

	public function failed(\Throwable $th): void
	{
		if ($th->getCode() === 999) {
			$this->release();
		} else {
			logger($th->getMessage());
			Logs::error($th, __LINE__, __FILE__);
		}
	}
}
