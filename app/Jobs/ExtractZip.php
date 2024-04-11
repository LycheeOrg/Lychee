<?php

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

	public string $filePath;
	public string $originalBaseName;
	public ?string $albumID;
	public int $userId;
	public ?int $fileLastModifiedTime;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		ProcessableJobFile $file,
		string|AbstractAlbum|null $albumID,
		?int $fileLastModifiedTime,
	) {
		$this->filePath = $file->getPath();
		$this->originalBaseName = $file->getOriginalBasename();
		$this->albumID = is_string($albumID) ? $albumID : $albumID?->id;
		$this->userId = Auth::user()->id;
		$this->fileLastModifiedTime = $fileLastModifiedTime;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->userId;
		$this->history->job = Str::limit('Extracting: ' . $this->originalBaseName, 200);
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

		$extractedFolderName = $this->getExtractFolderName();

		$pathExtracted = Storage::disk('extract-jobs')->path(date('Ymd') . $extractedFolderName);
		$zip = new \ZipArchive();
		if ($zip->open($this->filePath) === true) {
			$zip->extractTo($pathExtracted);
			$zip->close();

			// clean up the zip file
			unlink($this->filePath);

			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();
		} else {
			throw new ZipExtractionException($this->filePath, $pathExtracted);
		}

		$newAlbum = $this->createAlbum($extractedFolderName, $this->albumID);
		$jobs = [];
		foreach (new \DirectoryIterator($pathExtracted) as $fileInfo) {
			if ($fileInfo->isDot() || $fileInfo->isDir()) {
				continue;
			}

			$extractedFile = new ExtractedJobFile($fileInfo->getRealPath(), $fileInfo->getFilename());
			$jobs[] = new ProcessImageJob($extractedFile, $newAlbum, $fileInfo->getMTime());
		}

		$jobs[] = new CleanUpExtraction($pathExtracted);
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
	 * @param string      $newAlbumName
	 * @param string|null $parentID
	 *
	 * @return Album new album
	 */
	private function createAlbum(string $newAlbumName, ?string $parentID): Album
	{
		if (SmartAlbumType::tryFrom($parentID) !== null) {
			$parentID = null;
		}

		/** @var Album $parentAlbum */
		$parentAlbum = $parentID !== null ? Album::query()->findOrFail($parentID) : null; // in case no ID provided -> import to root folder
		$createAlbum = new Create($this->userId);

		return $createAlbum->create($this->prepareAlbumName($newAlbumName), $parentAlbum);
	}

	/**
	 * Todo Later: add renamer module.
	 *
	 * @param string $albumNameCandidate
	 *
	 * @return string
	 */
	private function prepareAlbumName(string $albumNameCandidate): string
	{
		return trim(str_replace('_', ' ', $albumNameCandidate));
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
		$baseNameWithoutExtension = substr($this->originalBaseName, 0, -4);

		// Save that one (is default if no existing folder found).
		$orignalName = str_replace(' ', '_', $baseNameWithoutExtension);

		// Iterate on that one.
		$candidateName = $orignalName;

		// count
		$i = 0;
		while (Storage::disk('extract-jobs')->exists(date('Ymd') . $candidateName)) {
			$candidateName = $orignalName . '_(' . $i . ')';
			$i++;
		}

		return $candidateName;
	}
}
