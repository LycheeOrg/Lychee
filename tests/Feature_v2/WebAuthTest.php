<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Models\Configs;
use App\Models\User;
use App\Models\WebAuthnCredential;
use App\Services\WebAuthn\CeremonyStore;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Support\WebAuthn\SoftAuthenticator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Feature 107 (FR-107-01 … FR-107-07): WebAuthn on `web-auth/webauthn-lib`.
 *
 * The recorded registration and login (FX-107-01) are the ones Laragear's
 * tests used, re-encoded as unpadded base64url as the front end now sends
 * them (FR-107-10). Scenarios no recording covers use SoftAuthenticator.
 */
class WebAuthTest extends BaseApiWithDataTest
{
	private const LOGIN_CREDENTIAL_ID = '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA';
	private const LOGIN_USER_HANDLE = '27117450ff81461d80331fb79c655f39';
	private const LOGIN_CHALLENGE = 'Zn8U3jEDA4Io3huf-yhYGg';
	private const LOGIN_AUTHENTICATOR_DATA = 'SZYN5YgOjGh0NBcPZHZgW4_krrmihjLHmVzzuoMdl2MBAAAAcw';
	private const LOGIN_CLIENT_DATA_JSON = 'eyJ0eXBlIjoid2ViYXV0aG4uZ2V0IiwiY2hhbGxlbmdlIjoiWm44VTNqRURBNElvM2h1Zi15aFlHZyIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlLCJvdGhlcl9rZXlzX2Nhbl9iZV9hZGRlZF9oZXJlIjoiZG8gbm90IGNvbXBhcmUgY2xpZW50RGF0YUpTT04gYWdhaW5zdCBhIHRlbXBsYXRlLiBTZWUgaHR0cHM6Ly9nb28uZ2wveWFiUGV4In0';
	private const LOGIN_SIGNATURE = 'MEQCIH2vd0cf9cNB4EL2OWZ7vs1diyC954ePQ0QIqP5rVyTwAiBCaicVZ2ex-Zb3keXl69oQSyu0w5r_EN2V5A8hsB1Jqw';
	private const LOGIN_MALFORMED_SIGNATURE = 'NEQCIH2vd0cf9cNB4EL2OWZ7vs1diyC954ePQ0QIqP5rVyTwAiBCaicVZ2ex-Zb3keXl69oQSyu0w5r_EN2V5A8hsB1Jqw';
	/** Signature counter in LOGIN_AUTHENTICATOR_DATA. */
	private const LOGIN_COUNTER = 115;

	private const REGISTER_CREDENTIAL_ID = 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg';
	private const REGISTER_CHALLENGE = 'Y7CVUuj2aBfZ3nKP_tS3YQ';
	private const REGISTER_USER_HANDLE = 'dfe5446bfd664e599b6140fafd426489';
	private const REGISTER_ATTESTATION_OBJECT = 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjESZYN5YgOjGh0NBcPZHZgW4_krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQJLnWwafI0lH4aOpLMlD4TzjLAtmS15lCYJMb2Xdc4vAFzunZ133EUIPcW0zQOTy3KCG59GUG7I-A3um70cvwoKlAQIDJiABIVggG6db341aZsq7-N1jdp54dhYwnUu7yVwq11480ItZ9bUiWCB9Eo20Bxc5uzrA4l8Ch97AG0P2zpmUTzmGx9YaJ3z7gg';
	private const REGISTER_CLIENT_DATA_JSON = 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiWTdDVlV1ajJhQmZaM25LUF90UzNZUSIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ';

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebAuthnCredentials();
		config(['app.url' => 'https://localhost', 'webauthn.relying_party.id' => null, 'webauthn.challenge.timeout' => 60]);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebAuthnCredentials();
		Session::flush();
		parent::tearDown();
	}

	// ------------------------------------------------------------------
	// Registration (FR-107-01 … FR-107-03)
	// ------------------------------------------------------------------

	/**
	 * S-107-01.
	 */
	public function testWebAuthnRegisterOptions(): void
	{
		Auth::loginUsingId($this->admin->id);

		$response = $this->postJson('WebAuthn::register/options');
		$this->assertOk($response);

		$stored = resolve(CeremonyStore::class)->pullCreationOptions($this->admin->id);
		$response->assertJson([
			'rp' => ['name' => 'Lychee', 'id' => 'localhost'],
			'authenticatorSelection' => ['userVerification' => 'discouraged', 'residentKey' => 'preferred'],
			'user' => [
				'id' => Base64UrlSafe::encodeUnpadded($stored->user->id),
				'name' => $this->admin->webAuthnUserName(),
				'displayName' => $this->admin->name,
			],
			'attestation' => 'none',
			'excludeCredentials' => [],
			'timeout' => 60000,
			'challenge' => Base64UrlSafe::encodeUnpadded($stored->challenge),
		]);
		$expected_algorithms = extension_loaded('sodium') ? [-7, -257, -8] : [-7, -257];
		self::assertSame($expected_algorithms, array_column($response->json('pubKeyCredParams'), 'alg'));
		self::assertSame(32, strlen($stored->challenge));
		self::assertSame(32, strlen($stored->user->id), 'a new user handle is 32 random bytes (FR-107-02)');
		$response->assertJsonMissingPath('authenticatorSelection.requireResidentKey');

		Auth::logout();
	}

	/**
	 * S-107-06: a second key gets the handle of the first; it is excluded from registration.
	 */
	public function testWebAuthnRegisterOptionsReuseUserHandle(): void
	{
		$this->createCredentials();
		Auth::loginUsingId($this->admin->id);

		$response = $this->postJson('WebAuthn::register/options');
		$this->assertOk($response);

		$response->assertJson([
			'user' => ['id' => self::LOGIN_USER_HANDLE],
			'excludeCredentials' => [['type' => 'public-key', 'id' => self::LOGIN_CREDENTIAL_ID]],
		]);

		Auth::logout();
	}

	/**
	 * S-107-02: a guest gets Lychee's usual 401 (Laragear answered 403).
	 */
	public function testWebAuthnRegisterOptionsUnauthorized(): void
	{
		$response = $this->postJson('WebAuthn::register/options');
		$this->assertUnauthorized($response);
	}

	/**
	 * S-107-03.
	 */
	public function testWebAuthnRegisterExecution(): void
	{
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions();

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertNoContent($response);

		$responseList = $this->actingAs($this->admin)->getJson('WebAuthn');
		$this->assertOk($responseList);
		$responseList->assertJson([0 => ['id' => self::REGISTER_CREDENTIAL_ID]]);

		$credential = WebAuthnCredential::query()->findOrFail(self::REGISTER_CREDENTIAL_ID);
		self::assertSame($this->admin->id, $credential->authenticatable_id);
		self::assertSame(self::REGISTER_USER_HANDLE, $credential->user_handle);
		self::assertSame('localhost', $credential->rp_id);
		self::assertSame('https://localhost', $credential->origin);
		self::assertSame('none', $credential->attestation_format);
		self::assertSame(0, $credential->counter);
		self::assertTrue($credential->hasReadablePublicKey());

		Auth::logout();
	}

	/**
	 * S-107-04.
	 */
	public function testWebAuthnRegisterExpired(): void
	{
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions();
		$this->travel(61)->seconds();

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnprocessable($response);
		$response->assertSee('expired');

		$this->assertDatabaseMissing('webauthn_credentials', ['id' => self::REGISTER_CREDENTIAL_ID]);

		Auth::logout();
	}

	/**
	 * S-107-04: without options in the session.
	 */
	public function testWebAuthnRegisterWithoutChallenge(): void
	{
		Auth::loginUsingId($this->admin->id);

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnprocessable($response);
		$response->assertSee('No pending WebAuthn challenge');

		Auth::logout();
	}

	/**
	 * S-107-04: the options are consumed by the first attempt.
	 */
	public function testWebAuthnRegisterReplay(): void
	{
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions();
		$this->assertNoContent($this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation()));
		WebAuthnCredential::query()->whereKey(self::REGISTER_CREDENTIAL_ID)->delete();

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnprocessable($response);
		$this->assertDatabaseMissing('webauthn_credentials', ['id' => self::REGISTER_CREDENTIAL_ID]);

		Auth::logout();
	}

	/**
	 * S-107-06.
	 */
	public function testWebAuthnRegisterDuplicate(): void
	{
		$this->createCredentials(id: self::REGISTER_CREDENTIAL_ID);
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions();

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnprocessable($response);
		$response->assertSee('already registered');
		self::assertSame(1, WebAuthnCredential::query()->count());

		Auth::logout();
	}

	/**
	 * Registration of a response for another challenge.
	 */
	public function testWebAuthnRegisterWrongChallenge(): void
	{
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions(challenge: 'AAAAAAAAAAAAAAAAAAAAAA');

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnprocessable($response);
		$response->assertSee('Invalid challenge.');
		$this->assertDatabaseMissing('webauthn_credentials', ['id' => self::REGISTER_CREDENTIAL_ID]);

		Auth::logout();
	}

	/**
	 * Malformed body.
	 */
	public function testWebAuthnRegisterValidation(): void
	{
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions();

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', ['id' => self::REGISTER_CREDENTIAL_ID, 'type' => 'public-key']);
		$this->assertUnprocessable($response);

		Auth::logout();
	}

	/**
	 * S-107-05: a guest gets Lychee's usual 401 (Laragear answered 403).
	 */
	public function testWebAuthnRegisterUnauthorized(): void
	{
		$this->putCreationOptions();

		$response = $this->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnauthorized($response);
		$this->assertDatabaseMissing('webauthn_credentials', ['id' => self::REGISTER_CREDENTIAL_ID]);
	}

	/**
	 * S-107-28: the admin asked for the options, then the session signed in as another account.
	 */
	public function testWebAuthnRegisterOptionsOfAnotherAccount(): void
	{
		Auth::loginUsingId($this->admin->id);
		$this->putCreationOptions();
		Auth::loginUsingId($this->userMayUpload1->id);

		$response = $this->actingAs($this->userMayUpload1)->postJson('WebAuthn::register', $this->recordedAttestation());
		$this->assertUnprocessable($response);
		$response->assertSee('No pending WebAuthn challenge');
		$this->assertDatabaseMissing('webauthn_credentials', ['id' => self::REGISTER_CREDENTIAL_ID]);

		Auth::logout();
	}

	// ------------------------------------------------------------------
	// Login options (FR-107-04)
	// ------------------------------------------------------------------

	/**
	 * S-107-07.
	 */
	public function testWebAuthLoginOptions(): void
	{
		$this->createCredentials();

		$response = $this->postJson('WebAuthn::login/options', ['user_id' => $this->admin->id]);
		$this->assertOk($response);

		$stored = resolve(CeremonyStore::class)->pullRequestOptions();
		$response->assertJson([
			'timeout' => 60000,
			'challenge' => Base64UrlSafe::encodeUnpadded($stored->challenge),
			'rpId' => 'localhost',
			'userVerification' => 'preferred',
			'allowCredentials' => [
				0 => [
					'id' => self::LOGIN_CREDENTIAL_ID,
					'type' => 'public-key',
				],
			],
		]);
	}

	/**
	 * S-107-08.
	 */
	public function testWebAuthLoginOptionsUsername(): void
	{
		$this->createCredentials();

		$response = $this->postJson('WebAuthn::login/options', ['username' => $this->admin->username]);
		$this->assertOk($response);

		$response->assertJson([
			'timeout' => 60000,
			'allowCredentials' => [
				0 => [
					'id' => self::LOGIN_CREDENTIAL_ID,
					'type' => 'public-key',
				],
			],
		]);
	}

	/**
	 * Feature 091, S-091-16 – with `email_login_enabled` the email address
	 * narrows the credential list just like the username (FR-091-14). S-107-09.
	 */
	public function testWebAuthLoginOptionsEmailWhenEmailLoginEnabled(): void
	{
		Configs::set('email_login_enabled', '1');
		$this->createCredentials();

		$response = $this->postJson('WebAuthn::login/options', ['username' => $this->admin->email]);
		$this->assertOk($response);

		$response->assertJson([
			'timeout' => 60000,
			'allowCredentials' => [
				0 => [
					'id' => self::LOGIN_CREDENTIAL_ID,
					'type' => 'public-key',
				],
			],
		]);

		Configs::set('email_login_enabled', '0');
	}

	/**
	 * Feature 091, S-091-16 – without the setting, the email address narrows
	 * nothing: the challenge falls back to the discoverable-credential flow. S-107-09.
	 */
	public function testWebAuthLoginOptionsEmailWhenEmailLoginDisabled(): void
	{
		Configs::set('email_login_enabled', '0');
		$this->createCredentials();

		$response = $this->postJson('WebAuthn::login/options', ['username' => $this->admin->email]);
		$this->assertOk($response);

		$response->assertJsonMissing(['allowCredentials' => []]);
		$response->assertJsonMissingPath('allowCredentials');
	}

	/**
	 * S-107-10.
	 */
	public function testWebAuthLoginOptionsAnonymous(): void
	{
		$this->createCredentials();

		$response = $this->postJson('WebAuthn::login/options', []);
		$this->assertOk($response);

		$stored = resolve(CeremonyStore::class)->pullRequestOptions();
		$response->assertJson([
			'timeout' => 60000,
			'challenge' => Base64UrlSafe::encodeUnpadded($stored->challenge),
		]);
		$response->assertJsonMissing(['allowCredentials' => []]);
		$response->assertJsonMissingPath('allowCredentials');
		self::assertSame([], $stored->allowCredentials);
	}

	/**
	 * A disabled credential is not offered.
	 */
	public function testWebAuthLoginOptionsSkipDisabledCredentials(): void
	{
		$this->createCredentials();
		WebAuthnCredential::query()->whereKey(self::LOGIN_CREDENTIAL_ID)->update(['disabled_at' => now()]);

		$response = $this->postJson('WebAuthn::login/options', ['user_id' => $this->admin->id]);
		$this->assertOk($response);
		$response->assertJsonMissingPath('allowCredentials');
	}

	/**
	 * S-107-18.
	 */
	public function testWebAuthLoginDisabledByConfiguration(): void
	{
		$this->createCredentials();
		config(['features.disable-webauthn' => true]);

		$this->assertForbidden($this->postJson('WebAuthn::login/options', ['user_id' => $this->admin->id]));
		$this->assertForbidden($this->postJson('WebAuthn::login', $this->recordedAssertion()));
		$this->assertGuest();

		config(['features.disable-webauthn' => false]);
	}

	// ------------------------------------------------------------------
	// Login (FR-107-05)
	// ------------------------------------------------------------------

	/**
	 * S-107-11: recorded U2F-style assertion, no user handle.
	 */
	public function testWebAuthLogin(): void
	{
		$this->createCredentials();
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertNoContent($response);

		$this->assertAuthenticatedAs($this->admin);
		self::assertSame(self::LOGIN_COUNTER, WebAuthnCredential::query()->findOrFail(self::LOGIN_CREDENTIAL_ID)->counter);
		self::assertFalse(WebAuthnCredential::query()->findOrFail(self::LOGIN_CREDENTIAL_ID)->backup_eligible);

		Auth::logout();
	}

	/**
	 * S-107-11: Laragear's front end sent an empty string for a missing user handle.
	 */
	public function testWebAuthLoginEmptyUserHandle(): void
	{
		$this->createCredentials();
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion(['userHandle' => '']));
		$this->assertNoContent($response);
		$this->assertAuthenticated();

		Auth::logout();
	}

	/**
	 * S-107-12: malformed signature.
	 */
	public function testWebAuthLoginWrongSignature(): void
	{
		$this->createCredentials();
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion(['signature' => self::LOGIN_MALFORMED_SIGNATURE]));
		$this->assertUnprocessable($response);
		$response->assertSee('WebAuthn login failed');

		$this->assertGuest();
	}

	/**
	 * S-107-12: valid signature by another key.
	 */
	public function testWebAuthLoginSignatureOfAnotherKey(): void
	{
		$owner = new SoftAuthenticator();
		$this->storeAuthenticator($owner, $this->admin);
		$impostor = new SoftAuthenticator($owner->credential_id, $owner->user_handle);
		$options = $this->putRequestOptions(challenge: Base64UrlSafe::encodeUnpadded(random_bytes(32)), credential_ids: []);

		$response = $this->postJson('WebAuthn::login', $impostor->assertion($options->challenge));
		$this->assertUnprocessable($response);
		$response->assertSee('Invalid signature');

		$this->assertGuest();
	}

	/**
	 * S-107-13.
	 */
	public function testWebAuthLoginWrongChallenge(): void
	{
		$this->createCredentials();
		$this->putRequestOptions(challenge: 'Xn8U3jEDA4Io3huf-yhYGg');

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('Invalid challenge.');

		$this->assertGuest();
	}

	/**
	 * S-107-14: the options are consumed by the first attempt.
	 */
	public function testWebAuthLoginReplay(): void
	{
		$this->createCredentials();
		$this->putRequestOptions();
		$this->assertNoContent($this->postJson('WebAuthn::login', $this->recordedAssertion()));
		Auth::logout();
		WebAuthnCredential::query()->whereKey(self::LOGIN_CREDENTIAL_ID)->update(['counter' => 0]);

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('No pending WebAuthn challenge');

		$this->assertGuest();
	}

	/**
	 * S-107-14.
	 */
	public function testWebAuthLoginExpired(): void
	{
		$this->createCredentials();
		$this->putRequestOptions();
		$this->travel(61)->seconds();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('expired');

		$this->assertGuest();
	}

	/**
	 * A counter that went backwards points at a cloned authenticator.
	 */
	public function testWebAuthLoginCounterGoingBackwards(): void
	{
		$this->createCredentials();
		WebAuthnCredential::query()->whereKey(self::LOGIN_CREDENTIAL_ID)->update(['counter' => self::LOGIN_COUNTER + 1]);
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('WebAuthn login failed');
		self::assertSame(self::LOGIN_COUNTER + 1, WebAuthnCredential::query()->findOrFail(self::LOGIN_CREDENTIAL_ID)->counter);

		$this->assertGuest();
	}

	/**
	 * S-107-15: discoverable credential, the response carries the user handle.
	 */
	public function testWebAuthLoginDiscoverable(): void
	{
		$authenticator = new SoftAuthenticator();
		$this->storeAuthenticator($authenticator, $this->userMayUpload1);
		$this->postJson('WebAuthn::login/options', []);
		$options = resolve(CeremonyStore::class)->pullRequestOptions();
		resolve(CeremonyStore::class)->putRequestOptions($options);

		$response = $this->postJson('WebAuthn::login', $authenticator->assertion($options->challenge, with_handle: true));
		$this->assertNoContent($response);

		$this->assertAuthenticatedAs($this->userMayUpload1);
		Auth::logout();
	}

	/**
	 * S-107-15: a user handle that is not the credential's.
	 */
	public function testWebAuthLoginDiscoverableWithForeignUserHandle(): void
	{
		$authenticator = new SoftAuthenticator();
		$this->storeAuthenticator($authenticator, $this->userMayUpload1);
		$options = $this->putRequestOptions(challenge: Base64UrlSafe::encodeUnpadded(random_bytes(32)), credential_ids: []);

		$response = $this->postJson('WebAuthn::login', $authenticator->assertion($options->challenge, with_handle: true, user_handle: random_bytes(32)));
		$this->assertUnprocessable($response);
		$response->assertSee('Invalid user handle');

		$this->assertGuest();
	}

	/**
	 * S-107-16: options asked for one user, assertion with another user's key.
	 */
	public function testWebAuthLoginCredentialNotAllowed(): void
	{
		$this->createCredentials();
		$other = new SoftAuthenticator();
		$this->storeAuthenticator($other, $this->userMayUpload1);
		$this->postJson('WebAuthn::login/options', ['user_id' => $this->admin->id]);
		$options = resolve(CeremonyStore::class)->pullRequestOptions();
		resolve(CeremonyStore::class)->putRequestOptions($options);

		$response = $this->postJson('WebAuthn::login', $other->assertion($options->challenge));
		$this->assertUnprocessable($response);
		$response->assertSee('The credential ID is not allowed.');

		$this->assertGuest();
	}

	/**
	 * S-107-17.
	 */
	public function testWebAuthLoginDisabledCredential(): void
	{
		$this->createCredentials();
		WebAuthnCredential::query()->whereKey(self::LOGIN_CREDENTIAL_ID)->update(['disabled_at' => now()]);
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('Unknown security key.');

		$this->assertGuest();
	}

	/**
	 * S-107-17.
	 */
	public function testWebAuthLoginUnknownCredential(): void
	{
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('Unknown security key.');

		$this->assertGuest();
	}

	/**
	 * Malformed body.
	 */
	public function testWebAuthLoginValidation(): void
	{
		$this->putRequestOptions();

		$this->assertUnprocessable($this->postJson('WebAuthn::login', ['id' => self::LOGIN_CREDENTIAL_ID, 'type' => 'public-key']));
		$this->assertUnprocessable($this->postJson('WebAuthn::login', [...$this->recordedAssertion(), 'type' => 'password']));

		$this->assertGuest();
	}

	/**
	 * S-107-29: every field has a length limit (FR-107-05).
	 */
	public function testWebAuthLoginFieldLengthLimits(): void
	{
		$assertion = $this->recordedAssertion();
		$oversized = [
			'id' => str_repeat('A', 1365),
			'rawId' => str_repeat('A', 1365),
			'response.authenticatorData' => str_repeat('A', 16385),
			'response.clientDataJSON' => str_repeat('A', 16385),
			'response.signature' => str_repeat('A', 16385),
			'response.userHandle' => str_repeat('A', 87),
		];

		foreach ($oversized as $field => $value) {
			$this->putRequestOptions();
			$payload = $assertion;
			data_set($payload, $field, $value);

			$response = $this->postJson('WebAuthn::login', $payload);
			$this->assertUnprocessable($response);
			$response->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'must not be greater than'));
		}

		$this->assertGuest();
	}

	/**
	 * S-107-22: the recorded key as Laragear stored it, converted by the migration.
	 */
	public function testWebAuthLoginAfterMigration(): void
	{
		$this->insertLaragearRow(Crypt::encryptString(WebAuthnCredentialMigrationTest::FIXTURE_PEM));
		$this->keyMigration()->up();
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertNoContent($response);
		$this->assertAuthenticatedAs($this->admin);

		Auth::logout();
	}

	/**
	 * FR-107-09: a key the migration could not convert has to be removed and registered again.
	 */
	public function testWebAuthLoginUnconvertedCredential(): void
	{
		$this->insertLaragearRow(Crypt::encryptString(WebAuthnCredentialMigrationTest::FIXTURE_PEM));
		$this->putRequestOptions();

		$response = $this->postJson('WebAuthn::login', $this->recordedAssertion());
		$this->assertUnprocessable($response);
		$response->assertSee('This security key has to be removed and registered again.');

		$this->assertGuest();
	}

	// ------------------------------------------------------------------
	// Management (FR-107-07)
	// ------------------------------------------------------------------

	/**
	 * Testing the Listing of credentials. S-107-19.
	 */
	public function testWebAuthListingUnautorized(): void
	{
		$this->createCredentials();

		$responseList = $this->getJson('WebAuthn');
		$this->assertUnauthorized($responseList);
	}

	/**
	 * Testing the Deletion of credentials. S-107-19.
	 */
	public function testWebAuthEditDeleteUnautorized(): void
	{
		$this->createCredentials();

		$responseEdit = $this->patchJson('WebAuthn', ['id' => self::LOGIN_CREDENTIAL_ID, 'alias' => 'something']);
		$this->assertUnauthorized($responseEdit);

		$responseDelete = $this->deleteJson('WebAuthn', ['id' => self::LOGIN_CREDENTIAL_ID]);
		$this->assertUnauthorized($responseDelete);
	}

	/**
	 * Testing the Deletion of credentials. S-107-19.
	 */
	public function testWebAuthEditDeleteAutorized(): void
	{
		$this->createCredentials();

		Auth::loginUsingId($this->admin->id);

		$responseEdit = $this->patchJson('WebAuthn', ['id' => self::LOGIN_CREDENTIAL_ID, 'alias' => 'something']);
		$this->assertNoContent($responseEdit);

		$responseList = $this->getJson('WebAuthn');
		$this->assertOk($responseList);
		self::assertEquals('something', $responseList->json()[0]['alias']);
		self::assertSame(['id', 'alias', 'created_at'], array_keys($responseList->json()[0]));

		$responseDelete = $this->deleteJson('WebAuthn', ['id' => self::LOGIN_CREDENTIAL_ID]);
		$this->assertNoContent($responseDelete);
		$this->assertDatabaseMissing('webauthn_credentials', ['id' => self::LOGIN_CREDENTIAL_ID]);
		Auth::logout();
	}

	/**
	 * A non-admin user must not be able to rename a credential which belongs
	 * to somebody else, even though they are allowed to edit their own
	 * settings.
	 *
	 * The credential ID is not a secret: the public `WebAuthn::login/options`
	 * endpoint hands it out for any given username. S-107-19.
	 *
	 * @return void
	 */
	public function testWebAuthEditCredentialOfOtherUser(): void
	{
		$this->createCredentials();

		// Retrieve the ID of the admin's credential the same way an attacker would.
		$responseOptions = $this->postJson('WebAuthn::login/options', ['username' => $this->admin->username]);
		$this->assertOk($responseOptions);
		$credential_id = $responseOptions->json()['allowCredentials'][0]['id'];

		$this->userMayUpload1->may_edit_own_settings = true;
		$this->userMayUpload1->save();

		Auth::loginUsingId($this->userMayUpload1->id);

		$responseEdit = $this->patchJson('WebAuthn', ['id' => $credential_id, 'alias' => 'hijacked']);
		$this->assertForbidden($responseEdit);

		Auth::logout();

		// The admin's credential must be untouched.
		$this->assertDatabaseHas('webauthn_credentials', ['id' => $credential_id, 'alias' => null]);
	}

	/**
	 * A non-admin user may rename their own credential.
	 *
	 * This covers the ownership branch of the authorization gate, which the
	 * admin test above short-circuits. S-107-19.
	 *
	 * @return void
	 */
	public function testWebAuthEditOwnCredentialAsNonAdmin(): void
	{
		$credential_id = 'BBBBBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg';
		$this->createCredentials($this->userMayUpload1, $credential_id);

		$this->userMayUpload1->may_edit_own_settings = true;
		$this->userMayUpload1->save();

		Auth::loginUsingId($this->userMayUpload1->id);

		$responseEdit = $this->patchJson('WebAuthn', ['id' => $credential_id, 'alias' => 'my own key']);
		$this->assertNoContent($responseEdit);

		$responseList = $this->getJson('WebAuthn');
		$this->assertOk($responseList);
		self::assertEquals('my own key', $responseList->json()[0]['alias']);

		Auth::logout();
	}

	/**
	 * An administrator may rename any credential. S-107-19.
	 *
	 * @return void
	 */
	public function testWebAuthAdminEditCredentialOfOtherUser(): void
	{
		$credential_id = 'AAAAAp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg';
		$this->createCredentials($this->userMayUpload1, $credential_id);

		Auth::loginUsingId($this->admin->id);

		$responseEdit = $this->patchJson('WebAuthn', ['id' => $credential_id, 'alias' => 'renamed by admin']);
		$this->assertNoContent($responseEdit);

		Auth::logout();

		$this->assertDatabaseHas('webauthn_credentials', ['id' => $credential_id, 'alias' => 'renamed by admin']);
	}

	/**
	 * Renaming a credential which does not exist at all must not leak
	 * anything either. S-107-19.
	 *
	 * @return void
	 */
	public function testWebAuthEditUnknownCredential(): void
	{
		$this->createCredentials();

		Auth::loginUsingId($this->admin->id);

		$responseEdit = $this->patchJson('WebAuthn', ['id' => 'this-credential-does-not-exist', 'alias' => 'something']);
		$this->assertNotFound($responseEdit);

		Auth::logout();
	}

	// ------------------------------------------------------------------
	// Helpers
	// ------------------------------------------------------------------

	/**
	 * The recorded key, stored as the migration converts it.
	 *
	 * @param User|null $owner the owner of the credential, defaults to the admin
	 * @param string    $id    the ID of the credential
	 */
	private function createCredentials(?User $owner = null, string $id = self::LOGIN_CREDENTIAL_ID): void
	{
		$credential = new WebAuthnCredential([
			'id' => $id,
			'user_handle' => self::LOGIN_USER_HANDLE,
			'alias' => null,
			'counter' => 0,
			'rp_id' => 'localhost',
			'origin' => 'https://localhost',
			'transports' => null,
			'aaguid' => '00000000-0000-0000-0000-000000000000',
			'public_key' => Base64UrlSafe::encodeUnpadded($this->keyMigration()->pemToCose(WebAuthnCredentialMigrationTest::FIXTURE_PEM)),
			'attestation_format' => 'none',
		]);
		($owner ?? $this->admin)->webAuthnCredentials()->save($credential);
	}

	private function storeAuthenticator(SoftAuthenticator $authenticator, User $owner): void
	{
		$credential = new WebAuthnCredential([
			'id' => $authenticator->credentialId(),
			'user_handle' => $authenticator->userHandle(),
			'counter' => 0,
			'rp_id' => 'localhost',
			'origin' => 'https://localhost',
			'public_key' => Base64UrlSafe::encodeUnpadded($authenticator->cosePublicKey()),
			'attestation_format' => 'none',
		]);
		$owner->webAuthnCredentials()->save($credential);
	}

	/**
	 * A row as Laragear left it: encrypted PEM.
	 */
	private function insertLaragearRow(string $encrypted_public_key): void
	{
		DB::table('webauthn_credentials')->insert([
			'id' => self::LOGIN_CREDENTIAL_ID,
			'authenticatable_type' => User::class,
			'authenticatable_id' => $this->admin->id,
			'user_handle' => self::LOGIN_USER_HANDLE,
			'counter' => 0,
			'rp_id' => 'https://localhost',
			'origin' => 'https://localhost',
			'aaguid' => '00000000-0000-0000-0000-000000000000',
			'public_key' => $encrypted_public_key,
			'attestation_format' => 'none',
			'created_at' => now(),
			'updated_at' => now(),
		]);
	}

	private function keyMigration(): Migration
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_11_04_000002_convert_webauthn_public_keys.php');

		return $migration;
	}

	/**
	 * Registration options the recorded attestation answers, asked for by the admin.
	 */
	private function putCreationOptions(string $challenge = self::REGISTER_CHALLENGE): void
	{
		resolve(CeremonyStore::class)->putCreationOptions(PublicKeyCredentialCreationOptions::create(
			rp: PublicKeyCredentialRpEntity::create('Lychee', 'localhost'),
			user: PublicKeyCredentialUserEntity::create($this->admin->webAuthnUserName(), Base64UrlSafe::decodeNoPadding(self::REGISTER_USER_HANDLE), $this->admin->name),
			challenge: Base64UrlSafe::decodeNoPadding($challenge),
			pubKeyCredParams: [
				PublicKeyCredentialParameters::create('public-key', -7),
				PublicKeyCredentialParameters::create('public-key', -257),
			],
			authenticatorSelection: AuthenticatorSelectionCriteria::create(
				userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED,
				residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
			),
			attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
			timeout: 60000,
		), $this->admin->id);
	}

	/**
	 * Login options the recorded assertion answers.
	 *
	 * @param list<string> $credential_ids allowed credentials (base64url)
	 */
	private function putRequestOptions(string $challenge = self::LOGIN_CHALLENGE, array $credential_ids = [self::LOGIN_CREDENTIAL_ID]): PublicKeyCredentialRequestOptions
	{
		$options = PublicKeyCredentialRequestOptions::create(
			challenge: Base64UrlSafe::decodeNoPadding($challenge),
			rpId: 'localhost',
			allowCredentials: array_map(
				fn (string $id): PublicKeyCredentialDescriptor => PublicKeyCredentialDescriptor::create('public-key', Base64UrlSafe::decodeNoPadding($id)),
				$credential_ids),
			userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
			timeout: 60000,
		);
		resolve(CeremonyStore::class)->putRequestOptions($options);

		return $options;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function recordedAttestation(): array
	{
		return [
			'id' => self::REGISTER_CREDENTIAL_ID,
			'rawId' => self::REGISTER_CREDENTIAL_ID,
			'response' => [
				'attestationObject' => self::REGISTER_ATTESTATION_OBJECT,
				'clientDataJSON' => self::REGISTER_CLIENT_DATA_JSON,
			],
			'type' => 'public-key',
		];
	}

	/**
	 * @param array<string,string|null> $response_overrides
	 *
	 * @return array<string,mixed>
	 */
	private function recordedAssertion(array $response_overrides = []): array
	{
		return [
			'id' => self::LOGIN_CREDENTIAL_ID,
			'rawId' => self::LOGIN_CREDENTIAL_ID,
			'response' => [
				'authenticatorData' => self::LOGIN_AUTHENTICATOR_DATA,
				'clientDataJSON' => self::LOGIN_CLIENT_DATA_JSON,
				'signature' => self::LOGIN_SIGNATURE,
				'userHandle' => null,
				...$response_overrides,
			],
			'type' => 'public-key',
		];
	}
}
