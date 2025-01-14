<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v1;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laragear\WebAuthn\ByteBuffer;
use Laragear\WebAuthn\Challenge\Challenge;
use Tests\AbstractTestCase;
use Tests\Traits\RequiresEmptyWebAuthnCredentials;

class WebAuthTest extends AbstractTestCase
{
	use RequiresEmptyWebAuthnCredentials;

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
		Auth::loginUsingId(1);

		$response = $this->postJson('/api/WebAuthn::register/options');
		$this->assertOk($response);

		// retrieve the challenge from the Session
		$challengeRetrieved = Session::get(config('webauthn.challenge.key'));
		$clg = $challengeRetrieved->data->toBase64Url();

		$response->assertJson([
			'rp' => ['name' => 'Lychee'],
			'authenticatorSelection' => ['userVerification' => 'discouraged'],
			'user' => ['name' => null, 'displayName' => 'admin'],
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
		$response = $this->postJson('/api/WebAuthn::register/options');
		$this->assertForbidden($response);
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegisterExecution(): void
	{
		Auth::loginUsingId(1);

		// create challenge and flash it in the Session.
		$challenge = new Challenge(
			data: ByteBuffer::fromBase64Url('Y7CVUuj2aBfZ3nKP_tS3YQ'),
			timeout: 60,
			verify: false,
			properties: ['user_uuid' => 'dfe5446bfd664e599b6140fafd426489', 'user_handle' => null]
		);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('/api/WebAuthn::register', [
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
		$responseList = $this->postJson('/api/WebAuthn::list');
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
		Auth::loginUsingId(1);

		// -100 ensures that we are expired.
		$challenge = new Challenge(ByteBuffer::fromBase64Url('Y7CVUuj2aBfZ3nKP_tS3YQ'), -100, false, []);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('/api/WebAuthn::register', [
			'id' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy_Cgg',
			'rawId' => 'kudbBp8jSUfho6ksyUPhPOMsC2ZLXmUJgkxvZd1zi8AXO6dnXfcRQg9xbTNA5PLcoIbn0ZQbsj4De6bvRy/Cgg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjESZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQJLnWwafI0lH4aOpLMlD4TzjLAtmS15lCYJMb2Xdc4vAFzunZ133EUIPcW0zQOTy3KCG59GUG7I+A3um70cvwoKlAQIDJiABIVggG6db341aZsq7+N1jdp54dhYwnUu7yVwq11480ItZ9bUiWCB9Eo20Bxc5uzrA4l8Ch97AG0P2zpmUTzmGx9YaJ3z7gg==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiWTdDVlV1ajJhQmZaM25LUF90UzNZUSIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ==',
			],
			'type' => 'public-key',
		]);
		$this->assertStatus($response, 422); // Challenge is expired

		$responseList = $this->postJson('/api/WebAuthn::list');
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

		$response = $this->postJson('/api/WebAuthn::register', [
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
		$response = $this->postJson('/api/WebAuthn::login/options', ['user_id' => 1]);
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
		$response = $this->postJson('/api/WebAuthn::login/options', ['username' => 'admin']);
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
		$response = $this->postJson('/api/WebAuthn::login/options', []);
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

		$response = $this->postJson('/api/WebAuthn::login', [
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

		$response = $this->postJson('/api/WebAuthn::login', [
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
		$this->assertStatus($response, 422);
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

		$response = $this->postJson('/api/WebAuthn::login', [
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
		$this->assertStatus($response, 422);
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

		$responseList = $this->postJson('/api/WebAuthn::list');
		$this->assertUnauthorized($responseList);
	}

	/**
	 * Testing the Deletion of credentials.
	 *
	 * @return void
	 */
	public function testWebAuthDeleteUnautorized(): void
	{
		$this->createCredentials();

		$responseDelete = $this->postJson('/api/WebAuthn::delete', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']);
		$this->assertUnauthorized($responseDelete);
	}

	/**
	 * Testing the Deletion of credentials.
	 *
	 * @return void
	 */
	public function testWebAuthDeleteAutorized(): void
	{
		$this->createCredentials();

		Auth::loginUsingId(1);
		$responseDelete = $this->postJson('/api/WebAuthn::delete', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']);
		$this->assertNoContent($responseDelete);
		Auth::logout();
		Session::flush();
	}

	/**
	 * Simple generation of credentials.
	 *
	 * @return void
	 */
	private function createCredentials(): void
	{
		/** @var User $user */
		$user = User::query()->find(1);

		// The attribute for public key is encrypted (not that it really matters, but still).
		// Therefore we cannot use a classic insert as this encryption relies on the secret app key.
		// This key is different at each run of the tests, therefore we store a public key here unencrypted
		$key = $user->makeWebAuthnCredential([
			'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',

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
