<?php

namespace App\Http\Controllers\Administration;

use App\ControllerFunctions\WebAuth\Delete as DeleteDevices;
use App\ControllerFunctions\WebAuth\GenerateAuthentication;
use App\ControllerFunctions\WebAuth\GenerateRegistration;
use App\ControllerFunctions\WebAuth\Lists as ListDevices;
use App\ControllerFunctions\WebAuth\VerifyAuthentication;
use App\ControllerFunctions\WebAuth\VerifyRegistration;
use App\Http\Controllers\Controller;
use DarkGhostHunter\Larapass\Http\WebAuthnRules;
use Illuminate\Http\Request;

class WebAuthController extends Controller
{
	use WebAuthnRules;

	/**
	 * @var GenerateRegistration
	 */
	private $generateRegistration;

	/**
	 * @var VerifyRegistration
	 */
	private $verifyRegistration;

	/**
	 * @var GenerateAuthentication
	 */
	private $generateAuthentication;

	/**
	 * @var VerifiyAuthentication
	 */
	private $verifyAuthentication;

	/**
	 * @var ListDevices
	 */
	private $listDevices;

	/**
	 * @var DeleteDevices
	 */
	private $deleteDevices;

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
	public function GenerateRegistration(Request $request)
	{
		return $this->generateRegistration->do();
	}

	public function VerifyRegistration(Request $request)
	{
		$data = $request->validate($this->attestationRules());

		return $this->verifyRegistration->do($data);
	}

	public function GenerateAuthentication(Request $request)
	{
		$user_id = $request->input('user_id');

		return $this->generateAuthentication->do($user_id);
	}

	public function VerifyAuthentication(Request $request)
	{
		$credential = $request->validate($this->assertionRules());

		return $this->verifyAuthentication->do($credential);
	}

	public function List()
	{
		return $this->listDevices->do();
	}

	public function Delete(Request $request)
	{
		$id = $request->validate(['id' => 'required|string']);

		return $this->deleteDevices->do($id);
	}
}
