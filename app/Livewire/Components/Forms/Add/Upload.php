<?php

namespace App\Livewire\Components\Forms\Add;

use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\Livewire\FileStatus;
use App\Enum\SmartAlbumType;
use App\Exceptions\PhotoSkippedException;
use App\Facades\Helpers;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ProcessImageJob;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use function Safe\ini_get;

/**
 * This defines the Upload Form used in modals.
 */
class Upload extends Component
{
	use WithFileUploads;
	use InteractWithModal;
	public const DISK_NAME = 'livewire-upload';

	/**
	 * @var string|null albumId of where to upload the picture
	 */
	public ?string $albumId = null;

	/** @var array<int,array{fileName:string,fileChunk:mixed,lastModified:int,progress:int,uuidName:string,extension:string,fileSize:int,stage:string}> */
	public array $uploads = [];

	public int $chunkSize;
	public int $parallelism;

	/**
	 * Mount the component.
	 *
	 * @param array{parentID:?string} $params
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		$this->albumId = $params[Params::PARENT_ID] ?? null;

		// remove smart albums => if we are in one: upload to unsorted (i.e. albumId = null)
		if (SmartAlbumType::tryFrom($this->albumId) !== null) {
			$this->albumId = null;
		}

		$album = $this->albumId === null ? null : Album::findOrFail($this->albumId);
		Gate::authorize(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $album]);

		$this->chunkSize = self::getUploadLimit();
		$this->parallelism = Configs::getValueAsInt('upload_processing_limit');
	}

	public function render(): View
	{
		return view('livewire.forms.add.upload');
	}

	/**
	 * @param TemporaryUploadedFile $value
	 * @param string                $key
	 *
	 * @return void
	 */
	public function updatedUploads($value, string $key): void
	{
		$keys = explode('.', $key);
		$index = intval($keys[0]);
		$attribute = $keys[1] ?? null;

		$fileDetails = $this->uploads[$index];
		// Initialize data if not existing.
		$fileDetails['extension'] ??= '.' . pathinfo($fileDetails['fileName'], PATHINFO_EXTENSION);
		$fileDetails['uuidName'] ??= strtr(base64_encode(random_bytes(12)), '+/', '-_') . $fileDetails['extension'];

		// Ensure data are set
		$this->uploads[$index]['extension'] = $fileDetails['extension'];
		$this->uploads[$index]['uuidName'] = $fileDetails['uuidName'];
		$this->uploads[$index]['stage'] = $fileDetails['stage'] ?? FileStatus::UPLOADING->value;
		$this->uploads[$index]['progress'] = $fileDetails['progress'] ?? 0;

		if ($attribute === 'fileChunk') {
			$fileDetails = $this->uploads[$index];
			/** @var TemporaryUploadedFile $chunkFile */
			$chunkFile = $fileDetails['fileChunk'];
			$final = new NativeLocalFile(Storage::disk(self::DISK_NAME)->path($fileDetails['uuidName']));
			$final->append($chunkFile->readStream());
			$chunkFile->delete();

			$curSize = $final->getFilesize();

			$this->uploads[$index]['progress'] = intval($curSize / $fileDetails['fileSize'] * 100);
			if ($this->uploads[$index]['progress'] === 100) {
				$this->uploads[$index]['stage'] = FileStatus::READY->value;
			}
		}

		$this->triggerProcessing();
	}

	public function triggerProcessing(): void
	{
		foreach ($this->uploads as $idx => $fileData) {
			if ($fileData['stage'] === FileStatus::READY->value) {
				$uploadedFile = new NativeLocalFile(Storage::disk(self::DISK_NAME)->path($fileData['uuidName']));
				$processableFile = new ProcessableJobFile(
					$fileData['extension'],
					$fileData['fileName']
				);
				$processableFile->write($uploadedFile->read());
				$uploadedFile->close();
				$uploadedFile->delete();
				$processableFile->close();
				// End of work-around

				try {
					$this->uploads[$idx]['stage'] = FileStatus::PROCESSING->value;

					if (Configs::getValueAsBool('use_job_queues')) {
						ProcessImageJob::dispatch($processableFile, $this->albumId, $fileData['lastModified']);
					} else {
						ProcessImageJob::dispatchSync($processableFile, $this->albumId, $fileData['lastModified']);
					}
					$this->uploads[$idx]['stage'] = FileStatus::DONE->value;
				} catch (PhotoSkippedException $e) {
					$this->uploads[$idx]['stage'] = FileStatus::SKIPPED->value;
				}
			}
		}
	}

	public static function getUploadLimit(): int
	{
		$size = Configs::getValueAsInt('upload_chunk_size');
		if ($size === 0) {
			$size = (int) min(
				Helpers::convertSize(ini_get('upload_max_filesize')),
				Helpers::convertSize(ini_get('post_max_size')),
				Helpers::convertSize(ini_get('memory_limit')) / 10
			);
		}

		/** @var array<int,string> $rules */
		$rules = FileUploadConfiguration::rules();
		$sizeRule = collect($rules)->first(fn ($rule) => Str::startsWith($rule, 'max:'), 'max:12288');
		$LivewireSizeLimit = intval(Str::substr($sizeRule, 4)) * 1024;

		return min($size, $LivewireSizeLimit);
	}

	/**
	 * Close the modal containing the Upload panel.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}
}
