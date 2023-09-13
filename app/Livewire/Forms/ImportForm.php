<?php

namespace App\Livewire\Forms;

use App\Actions\Photo\Strategies\ImportMode;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Models\Album;
use App\Models\Configs;
use App\Rules\RandomIDRule;
use Livewire\Form;
use function Safe\preg_match;

class ImportForm extends Form
{
	public ?Album $album;
	public ImportMode $importMode;

	public ?string $albumID;
	public string $path;
	/** @var array<int,string> */
	public array $paths;
	public bool $delete_imported;
	public bool $skip_duplicates;
	public bool $import_via_symlink;
	public bool $resync_metadata;

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @return array<string,mixed>
	 */
	protected function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::PATH_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PATH_ATTRIBUTE . '.*' => 'required|string|distinct',
			RequestAttribute::DELETE_IMPORTED_ATTRIBUTE => 'sometimes|boolean',
			RequestAttribute::SKIP_DUPLICATES_ATTRIBUTE => 'sometimes|boolean',
			RequestAttribute::IMPORT_VIA_SYMLINK_ATTRIBUTE => 'sometimes|boolean',
			RequestAttribute::RESYNC_METADATA_ATTRIBUTE => 'sometimes|boolean',
		];
	}

	/**
	 * split path into paths array.
	 *
	 * @return void
	 */
	public function prepare()
	{
		$subject = $this->path;

		// We split the given path string at unescaped spaces into an
		// array or more precisely we create an array whose entries
		// match strings with non-space characters or escaped spaces.
		// After splitting, the escaped spaces must be replaced by
		// proper spaces as escaping of spaces is a GUI-only thing to
		// allow input of several paths into a single input field.
		$pattern = '/(?:\\ |\S)+/';
		preg_match($pattern, $subject, $matches);

		// drop first element: matched elements start at index 1
		array_shift($matches);
		$this->paths = array_map(fn ($v) => str_replace('\\ ', ' ', $v), $matches);
	}

	/**
	 * Initialize form data.
	 *
	 * @param ?string $albumID
	 *
	 * @return void
	 */
	public function init(?string $albumID): void
	{
		$this->albumID = $albumID;
		$this->path = public_path('uploads/import/');
		$this->delete_imported = Configs::getValueAsBool('delete_imported');
		$this->import_via_symlink = !Configs::getValueAsBool('delete_imported') && Configs::getValueAsBool('import_via_symlink');
		$this->skip_duplicates = Configs::getValueAsBool('skip_duplicates');
		$this->resync_metadata = false;
	}

	/**
	 * After validation we can parse the values.
	 *
	 * @return void
	 */
	public function processValidatedValues(): void
	{
		$this->album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		$this->importMode = new ImportMode($this->delete_imported, $this->skip_duplicates, $this->import_via_symlink, $this->resync_metadata);
	}
}
