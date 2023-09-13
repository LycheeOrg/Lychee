<?php

namespace App\Livewire\Components\Forms\Add;

use App\Enum\Livewire\FileStatus;
use App\Exceptions\PhotoSkippedException;
use App\Facades\Helpers;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

use function Safe\fclose;
use function Safe\fopen;
use function Safe\fread;
use function Safe\fwrite;
use function Safe\ini_get;
use function Safe\unlink;

/**
 * This defines the Login Form used in modals.
 */
class Upload extends Component
{
	use WithFileUploads;

	/**
	 * @var string|null albumId of where to upload the picture
	 */
	public ?string $albumId = null;

	/** @var array<int,array{fileName:string,fileChunk:mixed,lastModified:int,progress:int,fileRef:TemporaryUploadedFile,uuid:string,fileSize:int,stage:FileStatus}> */
	public array $uploads = [];

	public int $chunkSize;

	/**
	 * Mount the component.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function mount(array $params = []): void
	{
		$this->albumId = $params['parentId'] ?? null;
		$size = Configs::getValueAsInt('upload_chunk_size');

		$this->chunkSize = $size !== 0 ? $size : (int) min(
			Helpers::convertSize(ini_get('upload_max_filesize')),
			Helpers::convertSize(ini_get('post_max_size')),
			Helpers::convertSize(ini_get('memory_limit')) / 10
		);
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
		if ($attribute === 'fileChunk') {
			$fileDetails = $this->uploads[$index];

			// Initialize data if not existing.
			$fileDetails['uuid'] ??= strtr(base64_encode(random_bytes(12)), '+/', '-_');
			$fileDetails['stage'] ??= FileStatus::UPLOADING;

			// Refresh uploads
			$this->uploads[$index]['uuid'] = $fileDetails['uuid'];
			$this->uploads[$index]['stage'] = $fileDetails['stage'];

			// $extension = '.' . pathinfo($fileDetails['fileName'], PATHINFO_EXTENSION);
			$finalPath = Storage::path('/livewire-tmp/' . $fileDetails['fileName']);

			// Chunk File
			$chunkName = $fileDetails['fileChunk']->getFileName();
			$chunkPath = Storage::path('/livewire-tmp/' . $chunkName);
			$chunk = fopen($chunkPath, 'rb');
			$buff = fread($chunk, $this->chunkSize);
			fclose($chunk);

			// Merge Together
			$final = fopen($finalPath, 'ab');
			fwrite($final, $buff);
			fclose($final);
			unlink($chunkPath);

			// Progress
			$curSize = Storage::size('/livewire-tmp/' . $fileDetails['fileName']);
			$this->uploads[$index]['progress'] = intval($curSize / $fileDetails['fileSize'] * 100);
			if ($this->uploads[$index]['progress'] === 100) {
				$this->uploads[$index]['fileRef'] =
					TemporaryUploadedFile::createFromLivewire(
						'/' . $fileDetails['fileName']);
				$this->uploads[$index]['stage'] = FileStatus::READY;
			}
		}

		$this->triggerProcessing();
	}

	public function triggerProcessing(): void
	{
		foreach ($this->uploads as $idx => $fileData) {
			if ($fileData['stage'] === FileStatus::READY) {
				// This code is a nasty work-around which should not exist.
				// PHP stores a temporary copy of the uploaded file without a file
				// extension.
				// Unfortunately, most of our methods pass around absolute file paths
				// instead of proper `File` object.
				// During the process we have a lot of code which tries to
				// re-determine the MIME type of the file based on the file path.
				// This is not only inefficient, but the original MIME type (of the
				// uploaded file) gets lost on the way.
				// As a work-around we store the uploaded file with a file extension.
				// Unfortunately, we cannot simply re-name the file, because this
				// might break due to permission problems for certain installation
				// if the temporarily uploaded file is stored in the system-global
				// temporary directory below another mount point or another Docker
				// image than the Lychee installation.
				// Hence, we must make a deep copy.
				// TODO: Remove this code again, if all other TODOs regarding MIME and file handling are properly refactored and we have stopped using absolute file paths as the least common denominator to pass around files.
				$uploadedFile = new UploadedFile($fileData['fileRef']);
				$processableFile = new ProcessableJobFile(
					$uploadedFile->getOriginalExtension(),
					$uploadedFile->getOriginalBasename()
				);
				$processableFile->write($uploadedFile->read());
				$uploadedFile->close();
				$uploadedFile->delete();
				$processableFile->close();
				// End of work-around

				try {
					$this->uploads[$idx]['stage'] = FileStatus::PROCESSING;

					if (Configs::getValueAsBool('use_job_queues')) {
						// TODO: replace 0 correct time/date
						ProcessImageJob::dispatch($processableFile, $this->albumId, $fileData['lastModified'] ?? 0);
					} else {
						// TODO: replace 0 correct time/date
						ProcessImageJob::dispatchSync($processableFile, $this->albumId, $fileData['lastModified'] ?? 0);
					}
					$this->uploads[$idx]['stage'] = FileStatus::DONE;
				} catch (PhotoSkippedException $e) {
					$this->uploads[$idx]['stage'] = FileStatus::SKIPPED;
				}
			}
		}
	}
}
