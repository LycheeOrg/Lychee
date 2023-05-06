<?php

namespace App\Http\Livewire\Forms;

use App\Exceptions\PhotoSkippedException;
use App\Image\Files\ProcessableJobFile;
use App\Image\Files\UploadedFile;
use App\Jobs\ProcessImageJob;
use App\Models\Configs;
use Livewire\WithFileUploads;

/**
 * This defines the Login Form used in modals.
 */
class Upload extends BaseForm
{
	use WithFileUploads;

	/**
	 * @var array<int,\Livewire\TemporaryUploadedFile>
	 */
	public $files = [];

	/**
	 * @var array<int,string>
	 */
	public $uploadedThumbs = [];

	/**
	 * @var array<int,string>
	 */
	public $skipped = [];

	/**
	 * @var string|null albumId of where to upload the picture
	 */
	public ?string $albumId = null;

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array
	 */
	protected function getRuleSet(): array
	{
		return [];
	}

	protected function updatedFiles()
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
						ProcessImageJob::dispatch($processableFile, $this->albumId);
					} else {
						ProcessImageJob::dispatchSync($processableFile, $this->albumId);
					}
				} catch (PhotoSkippedException $e) {
					$this->skipped[$idx] = true;
				}
			}
		}
	}

	/**
	 * Mount the component.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function mount(array $params = []): void
	{
		parent::mount($params);
		$this->render = '-upload';
		$this->albumId = $params['parentId'] ?? null;
	}

	public function submit(): void
	{
	}
}
