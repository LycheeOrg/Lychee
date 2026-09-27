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

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laragear\WebAuthn\ByteBuffer;
use Laragear\WebAuthn\Challenge\Challenge;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class WebAuthTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyWebAuthnCredentials();
		config(['app.url' => 'https://localhost']);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyWebAuthnCredentials();
		parent::tearDown();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegisterOptions(): void
	{
		// $this->admin->email = null;
		// $this->admin->username = 'admin';
		// $this->admin->save();

		Auth::loginUsingId($this->admin->id);

		$response = $this->postJson('WebAuthn::register/options');
		$this->assertOk($response);

		// retrieve the challenge from the Session
		$challengeRetrieved = Session::get(config('webauthn.challenge.key'));
		$clg = $challengeRetrieved->data->toBase64Url();

		$response->assertJson([
			'rp' => ['name' => 'Lychee'],
			'authenticatorSelection' => ['userVerification' => 'discouraged'],
			'user' => ['name' => $this->admin->email, 'displayName' => $this->admin->username],
			// 'user' => ['name' => null, 'displayName' => 'admin'],
			'pubKeyCredParams' => [['type' => 'public-key', 'alg' => -7], ['type' => 'public-key', 'alg' => -257]],
			'attestation' => 'none',
			'excludeCredentials' => [],
			'timeout' => 60000,
			'challenge' => $clg,
		]);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegisterOptionsUnauthorized(): void
	{
		$response = $this->postJson('WebAuthn::register/options');
		$this->assertForbidden($response);
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegisterExecution(): void
	{
		Auth::loginUsingId($this->admin->id);

		// create challenge and flash it in the Session.
		$challenge = new Challenge(
			data: ByteBuffer::fromBase64Url('Y7CVUuj2aBfZ3nKP_tS3YQ'),
			timeout: 60,
			verify: false,
			properties: ['user_uuid' => 'dfe5446bfd664e599b6140fafd426489', 'user_handle' => null]
		);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', [
			'id' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg',
			'rawId' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy/Cgg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjESZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQJLnWwafI0lH4aOpLMlD4TzjLAtmS15lCYJMb2Xdc4vAFzunZ133EUIPcW0zQOTy3KCG59GUG7I+A3um70cvwoKlAQIDJiABIVggG6db341aZsq7+N1jdp54dhYwnUu7yVwq11480ItZ9bUiWCB9Eo20Bxc5uzrA4l8Ch97AG0P2zpmUTzmGx9YaJ3z7gg==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiWTdDVlV1ajJhQmZaM25LUF90UzNZUSIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ==',
			],
			'type' => 'public-key',
		]);

		$this->assertNoContent($response); // code 204

		// Check that it is indeed in the list
		$responseList = $this->actingAs($this->admin)->getJson('WebAuthn');
		$this->assertOk($responseList); // code 200

		// Check that the key is indeed in the list
		$responseList->assertJson([
			0 => ['id' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg'],
		]);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegisterExpired(): void
	{
		Auth::loginUsingId($this->admin->id);

		// -100 ensures that we are expired.
		$challenge = new Challenge(ByteBuffer::fromBase64Url('Y7CVUuj2aBfZ3nKP_tS3YQ'), -100, false, []);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->actingAs($this->admin)->postJson('WebAuthn::register', [
			'id' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg',
			'rawId' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy/Cgg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjESZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQJLnWwafI0lH4aOpLMlD4TzjLAtmS15lCYJMb2Xdc4vAFzunZ133EUIPcW0zQOTy3KCG59GUG7I+A3um70cvwoKlAQIDJiABIVggG6db341aZsq7+N1jdp54dhYwnUu7yVwq11480ItZ9bUiWCB9Eo20Bxc5uzrA4l8Ch97AG0P2zpmUTzmGx9YaJ3z7gg==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiWTdDVlV1ajJhQmZaM25LUF90UzNZUSIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ==',
			],
			'type' => 'public-key',
		]);
		$this->assertUnprocessable($response); // Challenge is expired

		$responseList = $this->actingAs($this->admin)->getJson('WebAuthn');
		$this->assertOk($responseList); // code 200

		// check that the key has not been added to the list
		$responseList->assertJsonMissing([
			0 => ['id' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg'],
		]);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegisterUnauthorized(): void
	{
		// -100 ensures that we are expired.
		$challenge = new Challenge(ByteBuffer::fromBase64Url('Y7CVUuj2aBfZ3nKP_tS3YQ'), 60, false, []);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('WebAuthn::register', [
			'id' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg',
			'rawId' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy/Cgg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjESZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQJLnWwafI0lH4aOpLMlD4TzjLAtmS15lCYJMb2Xdc4vAFzunZ133EUIPcW0zQOTy3KCG59GUG7I+A3um70cvwoKlAQIDJiABIVggG6db341aZsq7+N1jdp54dhYwnUu7yVwq11480ItZ9bUiWCB9Eo20Bxc5uzrA4l8Ch97AG0P2zpmUTzmGx9YaJ3z7gg==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiWTdDVlV1ajJhQmZaM25LUF90UzNZUSIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ==',
			],
			'type' => 'public-key',
		]);
		$this->assertForbidden($response); // Not logged in.
	}

	/**
	 * Testing the Login options.
	 *
	 * @return void
	 */
	public function testWebAuthLoginOptions(): void
	{
		$this->createCredentials();

		// Generate a challenge for user_id = 1
		$response = $this->postJson('WebAuthn::login/options', ['user_id' => $this->admin->id]);
		$this->assertOk($response);

		$challengeRetrieved = Session::get(config('webauthn.challenge.key'));
		$clg = $challengeRetrieved->data->toBase64Url();

		$response->assertJson([
			'timeout' => 60000,
			'challenge' => $clg,
			'allowCredentials' => [
				0 => [
					'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',
					'type' => 'public-key',
				],
			],
		]);
	}

	/**
	 * Testing the Login options.
	 *
	 * @return void
	 */
	public function testWebAuthLoginOptionsUsername(): void
	{
		$this->createCredentials();

		// Generate a challenge for username = admin
		$response = $this->postJson('WebAuthn::login/options', ['username' => $this->admin->username]);
		$this->assertOk($response);

		$challengeRetrieved = Session::get(config('webauthn.challenge.key'));
		$clg = $challengeRetrieved->data->toBase64Url();

		$response->assertJson([
			'timeout' => 60000,
			'challenge' => $clg,
			'allowCredentials' => [
				0 => [
					'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',
					'type' => 'public-key',
				],
			],
		]);
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthLoginOptionsAnonymous(): void
	{
		$this->createCredentials();

		// Generate a challenge for user_id = 1
		$response = $this->postJson('WebAuthn::login/options', []);
		$this->assertOk($response);

		$challengeRetrieved = Session::get(config('webauthn.challenge.key'));
		$clg = $challengeRetrieved->data->toBase64Url();

		$response->assertJson([
			'timeout' => 60000,
			'challenge' => $clg,
		]);
		$response->assertJsonMissing(
			['allowCredentials' => []]
		);
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthLogin(): void
	{
		$this->createCredentials();

		// Override the challenge with precomputed data
		$challenge = new Challenge(
			data: ByteBuffer::fromBase64Url('Zn8U3jEDA4Io3huf-yhYGg'),
			timeout: 60,
			verify: false,
			properties: ['credentials' => ['_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']]
		);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('WebAuthn::login', [
			'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',
			'rawId' => '/Xlz+khgFhDdkvOWyy/YqC54ExkYyp1o6HAQiybqLST+9RGBndpgI06TQygIYI7ZL2dayCMYm6J1+bXyl72obA==',
			'response' => [
				'authenticatorData' => 'SZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2MBAAAAcw==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uZ2V0IiwiY2hhbGxlbmdlIjoiWm44VTNqRURBNElvM2h1Zi15aFlHZyIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlLCJvdGhlcl9rZXlzX2Nhbl9iZV9hZGRlZF9oZXJlIjoiZG8gbm90IGNvbXBhcmUgY2xpZW50RGF0YUpTT04gYWdhaW5zdCBhIHRlbXBsYXRlLiBTZWUgaHR0cHM6Ly9nb28uZ2wveWFiUGV4In0=',
				'signature' => 'MEQCIH2vd0cf9cNB4EL2OWZ7vs1diyC954ePQ0QIqP5rVyTwAiBCaicVZ2ex+Zb3keXl69oQSyu0w5r/EN2V5A8hsB1Jqw==',
				'userHandle' => '',
			],
			'type' => 'public-key',
		]);
		$this->assertNoContent($response);

		$this->assertAuthenticated();

		Auth::logout();
		Session::flush();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthLoginWrongSignature(): void
	{
		$this->createCredentials();

		// Override the challenge with precomputed data
		$challenge = new Challenge(
			data: ByteBuffer::fromBase64Url('Zn8U3jEDA4Io3huf-yhYGg'),
			timeout: 60,
			verify: false,
			properties: ['credentials' => ['_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']]
		);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('WebAuthn::login', [
			'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',
			'rawId' => '/Xlz+khgFhDdkvOWyy/YqC54ExkYyp1o6HAQiybqLST+9RGBndpgI06TQygIYI7ZL2dayCMYm6J1+bXyl72obA==',
			'response' => [
				'authenticatorData' => 'SZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2MBAAAAcw==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uZ2V0IiwiY2hhbGxlbmdlIjoiWm44VTNqRURBNElvM2h1Zi15aFlHZyIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlLCJvdGhlcl9rZXlzX2Nhbl9iZV9hZGRlZF9oZXJlIjoiZG8gbm90IGNvbXBhcmUgY2xpZW50RGF0YUpTT04gYWdhaW5zdCBhIHRlbXBsYXRlLiBTZWUgaHR0cHM6Ly9nb28uZ2wveWFiUGV4In0=',
				'signature' => 'NEQCIH2vd0cf9cNB4EL2OWZ7vs1diyC954ePQ0QIqP5rVyTwAiBCaicVZ2ex+Zb3keXl69oQSyu0w5r/EN2V5A8hsB1Jqw==',
				'userHandle' => '',
			],
			'type' => 'public-key',
		]);
		$this->assertUnprocessable($response);
		$response->assertSee('Assertion Error: Signature is invalid');

		$this->assertGuest();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthLoginWrongChallenge(): void
	{
		$this->createCredentials();

		// Override the challenge with precomputed data
		$challenge = new Challenge(
			data: ByteBuffer::fromBase64Url('Xn8U3jEDA4Io3huf-yhYGg'),
			timeout: 60,
			verify: false,
			properties: ['credentials' => ['_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']]
		);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('WebAuthn::login', [
			'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',
			'rawId' => '/Xlz+khgFhDdkvOWyy/YqC54ExkYyp1o6HAQiybqLST+9RGBndpgI06TQygIYI7ZL2dayCMYm6J1+bXyl72obA==',
			'response' => [
				'authenticatorData' => 'SZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2MBAAAAcw==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uZ2V0IiwiY2hhbGxlbmdlIjoiWm44VTNqRURBNElvM2h1Zi15aFlHZyIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlLCJvdGhlcl9rZXlzX2Nhbl9iZV9hZGRlZF9oZXJlIjoiZG8gbm90IGNvbXBhcmUgY2xpZW50RGF0YUpTT04gYWdhaW5zdCBhIHRlbXBsYXRlLiBTZWUgaHR0cHM6Ly9nb28uZ2wveWFiUGV4In0=',
				'signature' => 'NEQCIH2vd0cf9cNB4EL2OWZ7vs1diyC954ePQ0QIqP5rVyTwAiBCaicVZ2ex+Zb3keXl69oQSyu0w5r/EN2V5A8hsB1Jqw==',
				'userHandle' => '',
			],
			'type' => 'public-key',
		]);
		$this->assertUnprocessable($response);
		$response->assertSee('Assertion Error: Response challenge is not equal.');

		$this->assertGuest();
	}

	/**
	 * Testing the Listing of credentials.
	 *
	 * @return void
	 */
	public function testWebAuthListingUnautorized(): void
	{
		$this->createCredentials();

		$responseList = $this->getJson('WebAuthn');
		$this->assertUnauthorized($responseList);
	}

	/**
	 * Testing the Deletion of credentials.
	 *
	 * @return void
	 */
	public function testWebAuthEditDeleteUnautorized(): void
	{
		$this->createCredentials();

		$responseEdit = $this->patchJson('WebAuthn', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA', 'alias' => 'something']);
		$this->assertUnauthorized($responseEdit);

		$responseDelete = $this->deleteJson('WebAuthn', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']);
		$this->assertUnauthorized($responseDelete);
	}

	/**
	 * Testing the Deletion of credentials.
	 *
	 * @return void
	 */
	public function testWebAuthEditDeleteAutorized(): void
	{
		$this->createCredentials();

		Auth::loginUsingId($this->admin->id);

		$responseEdit = $this->patchJson('WebAuthn', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA', 'alias' => 'something']);
		$this->assertNoContent($responseEdit);

		$responseList = $this->getJson('WebAuthn');
		$this->assertOk($responseList);
		self::assertEquals('something', $responseList->json()[0]['alias']);

		$responseDelete = $this->deleteJson('WebAuthn', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']);
		$this->assertNoContent($responseDelete);
		Auth::logout();
		Session::flush();
	}

	/**
	 * A non-admin user must not be able to rename a credential which belongs
	 * to somebody else, even though they are allowed to edit their own
	 * settings.
	 *
	 * The credential ID is not a secret: the public `WebAuthn::login/options`
	 * endpoint hands it out for any given username.
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
		Session::flush();

		// The admin's credential must be untouched.
		$this->assertDatabaseHas('webauthn_credentials', ['id' => $credential_id, 'alias' => null]);
	}

	/**
	 * A non-admin user may rename their own credential.
	 *
	 * This covers the ownership branch of the authorization gate, which the
	 * admin test above short-circuits.
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
		Session::flush();
	}

	/**
	 * An administrator may rename any credential.
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
		Session::flush();

		$this->assertDatabaseHas('webauthn_credentials', ['id' => $credential_id, 'alias' => 'renamed by admin']);
	}

	/**
	 * Renaming a credential which does not exist at all must not leak
	 * anything either.
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
		Session::flush();
	}

	/**
	 * Simple generation of credentials.
	 *
	 * @param User|null $owner the owner of the credential, defaults to the admin
	 * @param string    $id    the ID of the credential
	 *
	 * @return void
	 */
	private function createCredentials(?User $owner = null, string $id = '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA'): void
	{
		// The attribute for public key is encrypted (not that it really matters, but still).
		// Therefore we cannot use a classic insert as this encryption relies on the secret app key.
		// This key is different at each run of the tests, therefore we store a public key here unencrypted
		$key = ($owner ?? $this->admin)->makeWebAuthnCredential([
			'id' => $id,

			'user_id' => '27117450ff81461d80331fb79c655f39',
			'alias' => null,

			'counter' => 0,
			'rp_id' => 'https://localhost',
			'origin' => 'https://localhost',
			'transports' => null,
			'aaguid' => '00000000-0000-0000-0000-000000000000',

			'public_key' => "-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEF25MWDQwaeFqZJ2Gy/7HEdZkWaW+\nQCbWjLiplbklmqIq6MSSRhLqJLoegR5PqG2JOqhSLcQDAmf/tzdAvO5MmQ==\n-----END PUBLIC KEY-----\n",
			'attestation_format' => 'none',
		]);
		$key->save();
	}
}
