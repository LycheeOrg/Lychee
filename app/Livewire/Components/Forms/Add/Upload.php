<?php

namespace App\Livewire\Components\Forms\Add;

use App\Exceptions\PhotoSkippedException;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * This defines the Login Form used in modals.
 */
class Upload extends Component
{
	use WithFileUploads;

	/** @var array<int,TemporaryUploadedFile> */
	public $files = [];

	/**
	 * @var array<int,string>
	 */
	public $uploadedThumbs = [];

	/**
	 * @var array<int,bool>
	 */
	public $skipped = [];

	/**
	 * @var string|null albumId of where to upload the picture
	 */
	public ?string $albumId = null;

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
	}

	protected function updatedFiles(): void
	{
		foreach ($this->files as $idx => $file) {
			if (!array_key_exists($idx, $this->uploadedThumbs) && !array_key_exists($idx, $this->skipped)) {
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
				$uploadedFile = new UploadedFile($file);
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
					$this->uploadedThumbs[$idx] = 'img/placeholder.png';
					if (Configs::getValueAsBool('use_job_queues')) {
						// TODO: replace 0 correct time/date
						ProcessImageJob::dispatch($processableFile, $this->albumId, 0);
					} else {
						// TODO: replace 0 correct time/date
						ProcessImageJob::dispatchSync($processableFile, $this->albumId, 0);
					}
				} catch (PhotoSkippedException $e) {
					$this->skipped[$idx] = true;
				}
			}
		}
	}

	public function render(): View
	{
		return view('livewire.forms.add.upload');
	}
}
