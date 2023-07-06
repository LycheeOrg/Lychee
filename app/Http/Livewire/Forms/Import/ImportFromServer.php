<?php

namespace App\Http\Livewire\Forms\Import;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Exceptions\Internal\QueryBuilderException;
use App\Http\Livewire\Forms\BaseForm;
use App\Http\RuleSets\Import\ImportServerRuleSet;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;
use function Safe\preg_match;

/**
 * This defines the Login Form used in modals.
 */
class ImportFromServer extends BaseForm
{
	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array
	 */
	protected function getRuleSet(): array
	{
		return ImportServerRuleSet::rules();
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
		$this->render = '-import-from-server';

		// Initialize form elements (and dependent form elements) based on
		// global configuration settings.
		$this->form = [
			RequestAttribute::PATH_ATTRIBUTE => public_path('uploads/import/'),
			RequestAttribute::DELETE_IMPORTED_ATTRIBUTE => Configs::getValueAsBool('delete_imported'),
			RequestAttribute::IMPORT_VIA_SYMLINK_ATTRIBUTE => !Configs::getValueAsBool('delete_imported') && Configs::getValueAsBool('import_via_symlink'),
			RequestAttribute::SKIP_DUPLICATES_ATTRIBUTE => Configs::getValueAsBool('skip_duplicates'),
			RequestAttribute::RESYNC_METADATA_ATTRIBUTE => false,
		];
	}

	/**
	 * Hook the submit button.
	 *
	 * @return void
	 *
	 * @throws \Throwable
	 * @throws ValidationException
	 * @throws BindingResolutionException
	 * @throws \InvalidArgumentException
	 * @throws QueryBuilderException
	 */
	public function submit(): void
	{
		// Empty error bag
		$this->resetErrorBag();

		// prepare
		$subject = $this->form[RequestAttribute::PATH_ATTRIBUTE];

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
		$this->form[RequestAttribute::PATH_ATTRIBUTE] = array_map(fn ($v) => str_replace('\\ ', ' ', $v), $matches);

		// Validate
		// $values = $this->validate()['form'];
		// TODO: Return streamed response ?
	}
}
