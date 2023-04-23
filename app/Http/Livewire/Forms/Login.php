<?php

namespace App\Http\Livewire\Forms;

use App\Exceptions\Internal\QueryBuilderException;
use App\Http\RuleSets\LoginRuleSet;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\GitHubVersion;
use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * This defines the Login Form used in modals.
 */
class Login extends BaseForm
{
	public bool $is_new_release_available = false;
	public bool $is_git_update_available = false;
	public ?string $version = null;

	/**
	 * This defines the set of validation rules to be applied on the input.
	 * It would be a good idea to unify (namely reuse) the rules from the JSON api.
	 *
	 * @return array
	 */
	protected function getRuleSet(): array
	{
		return LoginRuleSet::rules();
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
		$this->render = '-login'; // We use specialized blade because of the version

		if (!Configs::getValueAsBool('hide_version_number')) {
			$this->version = resolve(InstalledVersion::class)->getVersion()->toString();
		}

		$fileVersion = resolve(FileVersion::class);
		$gitHubVersion = resolve(GitHubVersion::class);
		if (Configs::getValueAsBool('check_for_updates')) {
			$fileVersion->hydrate();
			$gitHubVersion->hydrate();
		}
		$this->is_new_release_available = !$fileVersion->isUpToDate();
		$this->is_git_update_available = !$gitHubVersion->isUpToDate();
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

		// Call Livewire validation on the from
		$data = $this->validate()['form'];

		// apply login as admin and trigger a reload
		if (Auth::attempt(['username' => $data['username'], 'password' => $data['password']])) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $data['username'] . ') has logged in from ' . request()->ip());
			$this->emitTo('index', 'reloadPage');

			return;
		}

		// Wrong login: stay on the modal and update the rendering.
		$this->addError('wrongLogin', 'Wrong login or password.');
		Logs::error(__METHOD__, __LINE__, 'User (' . $data['username'] . ') has tried to log in from ' . request()->ip());
	}
}
