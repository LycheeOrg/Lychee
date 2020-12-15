<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;
use DarkGhostHunter\Larapass\Http\WebAuthnRules;
use DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
	use WebAuthnRules;

	public function __construct()
	{
		$this->middleware([]);
	}

	/**
	 * You can manage the user credentials thanks to the WebAuthnAuthenticatable contract directly from within the user instance. The most useful methods are:.
	 *
	 * hasCredential(): Checks if the user has a given Credential ID.
	 * addCredential(): Adds a new Credential Source.
	 * removeCredential(): Removes an existing Credential by its ID.
	 * flushCredentials(): Removes all credentials. You can exclude credentials by their id.
	 * enableCredential(): Includes an existing Credential ID from authentication.
	 * disableCredential(): Excludes an existing Credential ID from authentication.
	 * getFromCredentialId(): Returns the user using the given Credential ID, if any.
	 */
	public function GenerateRegistration(Request $request)
	{
		/**
		 * @var User
		 */
		$user = Auth::user();

		// Create an attestation for a given user.
		return WebAuthn::generateAttestation($user);
	}

	public function VerifyRegistration(Request $request)
	{
		/**
		 * @var User
		 */
		$user = Auth::user();

		// okay.
		$credential = WebAuthn::validateAttestation(
			$request->validate($this->attestationRules()),
			$user
		);
		if ($credential) {
			$user->addCredential($credential);
		} else {
			return 'Something went wrong with your device!';
		}
	}

	public function GenerateAuthentication(Request $request)
	{
		// Find the user to assert, if there is any
		// $user = User::where('username', $request->input('username'))->first();
		$user = User::find(0);

		// Create an assertion for the given user (or a blank one if not found);
		return WebAuthn::generateAssertion($user);
	}

	/**
	 * Return the user that should authenticate via WebAuthn.
	 *
	 * @param array $credentials
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|\DarkGhostHunter\Larapass\Contracts\WebAuthnAuthenticatable|null
	 */
	protected function getUserFromCredentials(array $credentials)
	{
		// We will try to ask the User Provider for any user for the given credentials.
		// If there is one, we will then return an array of credentials ID that the
		// authenticator may use to sign the subsequent challenge by the server.
		return $this->userProvider()->retrieveByCredentials($credentials);
	}

	/**
	 * Get the User Provider for WebAuthn Authenticatable users.
	 *
	 * @return \Illuminate\Contracts\Auth\UserProvider
	 */
	protected function userProvider()
	{
		return Auth::createUserProvider('users');
	}

	public function VerifyAuthentication(Request $request)
	{
		// Verify the incoming assertion.
		$credential = $request->validate($this->assertionRules());
		// Debugbar::notice($credential);
		// $credential['userHandle'] ??= '';
		$cred = WebAuthn::validateAssertion($credential);
		// Debugbar::warning($cred);

		// If is valid, login the user of the credentials.
		if ($cred) {
			Debugbar::info('------------------ VICTORY ----------------');
			$user = $this->getUserFromCredentials($credential);
			Debugbar::info($user);
			Auth::login($user);
		}
	}
}
