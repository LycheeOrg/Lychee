<?php

namespace App\Http\Controllers\Install;

use App\Actions\InstallUpdate\Pipes\ArtisanKeyGenerate;
use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use App\Actions\InstallUpdate\Pipes\ArtisanViewClear;
use App\Actions\InstallUpdate\Pipes\QueryExceptionChecker;
use App\Actions\InstallUpdate\Pipes\Spacer;
use App\Exceptions\InstallationFailedException;
use App\Exceptions\Internal\FrameworkException;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Models\User;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

/**
 * Class MigrationController.
 */
class MigrationController extends Controller
{
	/**
	 * Migrates the Lychee DB and generates a new API key.
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 */
	public function view(Request $request): View
	{
		$values = $request->validate([
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		]);

		$output = [];
		$hasErrors = false;
		try {
			$output = app(Pipeline::class)
				->send($output)
				->through([
					ArtisanViewClear::class,
					ArtisanMigrate::class,
					QueryExceptionChecker::class,
					Spacer::class,
					ArtisanKeyGenerate::class,
					Spacer::class,
				])
				->thenReturn();
		} catch (InstallationFailedException) {
			$hasErrors = true;
		}

		if (!$hasErrors) {
			$user = new User();
			$user->may_upload = true;
			$user->may_edit_own_settings = true;
			$user->may_administrate = true;
			$user->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
			$user->password = Hash::make($values[RequestAttribute::PASSWORD_ATTRIBUTE]);
			$user->save();
		}

		try {
			return view('install.migrate', [
				'title' => 'Lychee-installer',
				'step' => 4,
				'lines' => $output,
				'errors' => $hasErrors,
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}
}
