<?php

namespace App\Livewire\Forms;

use App\Actions\Photo\Strategies\ImportMode;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Models\Album;
use App\Models\Configs;
use App\Rules\RandomIDRule;
use Livewire\Form;
use function Safe\preg_replace;
use function Safe\preg_split;

class ImportForm extends Form
{
	public ?string $albumID;
	public string $path;
	/** @var array<int,string> */
	public array $paths = [];
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
		$matches = $this->split_escaped(' ', '\\', $subject);

		// drop first element: matched elements start at index 1
		// array_shift($matches);
		$this->paths = array_map(fn ($v) => str_replace('\\ ', ' ', $v), $matches);
	}

	/**
	 * Dark magic code from Stack Overflow
	 * https://stackoverflow.com/a/27135602.
	 *
	 * @param string $delimiter
	 * @param string $escaper
	 * @param string $text
	 *
	 * @return string[]
	 */
	private function split_escaped(string $delimiter, string $escaper, string $text): array
	{
		$d = preg_quote($delimiter, '~');
		$e = preg_quote($escaper, '~');
		$tokens = preg_split(
			'~' . $e . '(' . $e . '|' . $d . ')(*SKIP)(*FAIL)|' . $d . '~',
			$text
		);
		$escaperReplacement = str_replace(['\\', '$'], ['\\\\', '\\$'], $escaper);
		$delimiterReplacement = str_replace(['\\', '$'], ['\\\\', '\\$'], $delimiter);

		return preg_replace(
			['~' . $e . $e . '~', '~' . $e . $d . '~'],
			[$escaperReplacement, $delimiterReplacement],
			$tokens
		);
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

	public function getAlbum(): null|Album
	{
		/** @var Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		return $album;
	}

	public function getImportMode(): ImportMode
	{
		return new ImportMode($this->delete_imported, $this->skip_duplicates, $this->import_via_symlink, $this->resync_metadata);
	}
}
