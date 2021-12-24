<?php

namespace App\Http\Controllers\Administration;

use App\Actions\WebAuth\Delete as DeleteDevices;
use App\Actions\WebAuth\GenerateAuthentication;
use App\Actions\WebAuth\GenerateRegistration;
use App\Actions\WebAuth\Lists as ListDevices;
use App\Actions\WebAuth\VerifyAuthentication;
use App\Actions\WebAuth\VerifyRegistration;
use App\Exceptions\Internal\InvalidUserIdException;
use App\Exceptions\UnauthenticatedException;
use DarkGhostHunter\Larapass\Http\WebAuthnRules;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

class WebAuthController extends Controller
{
	use WebAuthnRules;

	private GenerateRegistration $generateRegistration;
	private VerifyRegistration $verifyRegistration;
	private GenerateAuthentication $generateAuthentication;
	private VerifyAuthentication $verifyAuthentication;
	private ListDevices $listDevices;
	private DeleteDevices $deleteDevices;

	public function __construct(
		GenerateRegistration $generateRegistration,
		VerifyRegistration $verifyRegistration,
		GenerateAuthentication $generateAuthentication,
		VerifyAuthentication $verifyAuthentication,
		ListDevices $listDevices,
		DeleteDevices $deleteDevices
	) {
		$this->generateRegistration = $generateRegistration;
		$this->verifyRegistration = $verifyRegistration;
		$this->generateAuthentication = $generateAuthentication;
		$this->verifyAuthentication = $verifyAuthentication;
		$this->listDevices = $listDevices;
		$this->deleteDevices = $deleteDevices;
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
	public function generateRegistration(): PublicKeyCredentialCreationOptions
	{
		return $this->generateRegistration->do();
	}

	public function verifyRegistration(Request $request): void
	{
		$data = $request->validate($this->attestationRules());

		$this->verifyRegistration->do($data);
	}

	public function generateAuthentication(Request $request): PublicKeyCredentialRequestOptions
	{
		return $this->generateAuthentication->do($request['user_id']);
	}

	/**
	 * @throws UnauthenticatedException
	 * @throws InvalidUserIdException
	 */
	public function verifyAuthentication(Request $request): void
	{
		$credential = $request->validate($this->assertionRules());

		$this->verifyAuthentication->do($credential);
	}

	public function list(): Collection
	{
		return $this->listDevices->do();
	}

	public function delete(Request $request): void
	{
		$id = $request->validate(['id' => 'required|string']);
		$this->deleteDevices->do($id);
	}
}
