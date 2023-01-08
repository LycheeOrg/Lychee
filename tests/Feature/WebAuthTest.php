<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laragear\WebAuthn\ByteBuffer;
use Laragear\WebAuthn\Challenge;
use Tests\AbstractTestCase;
use Tests\Feature\Traits\CatchFailures;

class WebAuthTest extends AbstractTestCase
{
	use CatchFailures;

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthnRegister(): void
	{
		Auth::loginUsingId(1);
		$response = $this->postJson('/api/WebAuthn::register/options');
		$this->assertOk($response);

		$challenge = new Challenge(
			data: ByteBuffer::fromBase64('SRgqQgFXqbBblB85yXld9Q'),
			timeout: 60,
			verify: false,
			properties: ['user_uuid' => 'dfe5446bfd664e599b6140fafd426489', 'user_handle' => null]
		);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('/api/WebAuthn::register', [
			'id' => 'Pzcdil3rP3D-abaH84oyq9lsbZzbL9_e7HhS-Z-PvjprTCSuTlkhgIAYhjOUPQCQwn0o_QMBo-bPDT-SWU6rPg',
			'rawId' => 'Pzcdil3rP3D+abaH84oyq9lsbZzbL9/e7HhS+Z+PvjprTCSuTlkhgIAYhjOUPQCQwn0o/QMBo+bPDT+SWU6rPg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjEc0C5YcCE9mphY/zz0IU/zq4zv5kiobJsNcgdhuZXNrJBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQD83HYpd6z9w/mm2h/OKMqvZbG2c2y/f3ux4Uvmfj746a0wkrk5ZIYCAGIYzlD0AkMJ9KP0DAaPmzw0/kllOqz6lAQIDJiABIVggpdxXAuNaZVOlkPFshNfXW15VNIIQ2piLQbRhXZJlD8MiWCCRQN/b6NlruWWuPf5bHrXU0B5Y6sHMkpP/UBBdbsNOhg==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiU1JncVFnRlhxYkJibEI4NXlYbGQ5USIsIm9yaWdpbiI6Imh0dHBzOi8vbHljaGVlLnRlc3QiLCJjcm9zc09yaWdpbiI6ZmFsc2V9',
			],
			'type' => 'public-key',
		]);

		$this->assertNoContent($response); // code 204

		$responseList = $this->postJson('/api/WebAuthn::list');
		$this->assertOk($responseList); // code 200

		// Check that the key is indeed in the list
		$responseList->assertJson([
			0 => ['id' => 'Pzcdil3rP3D-abaH84oyq9lsbZzbL9_e7HhS-Z-PvjprTCSuTlkhgIAYhjOUPQCQwn0o_QMBo-bPDT-SWU6rPg'],
		]);

		$responseDelete = $this->postJson('/api/WebAuthn::delete', ['id' => 'Pzcdil3rP3D-abaH84oyq9lsbZzbL9_e7HhS-Z-PvjprTCSuTlkhgIAYhjOUPQCQwn0o_QMBo-bPDT-SWU6rPg']);
		$this->assertNoContent($responseDelete);

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
		$response = $this->postJson('/api/WebAuthn::register/options');
		$this->assertOk($response);

		// -100 ensures that we are expired.
		$challenge = new Challenge(ByteBuffer::fromBase64('SRgqQgFXqbBblB85yXld9Q'), -100, false, []);
		Session::put(config('webauthn.challenge.key'), $challenge);

		$response = $this->postJson('/api/WebAuthn::register', [
			'id' => 'Pzcdil3rP3D-abaH84oyq9lsbZzbL9_e7HhS-Z-PvjprTCSuTlkhgIAYhjOUPQCQwn0o_QMBo-bPDT-SWU6rPg',
			'rawId' => 'Pzcdil3rP3D+abaH84oyq9lsbZzbL9/e7HhS+Z+PvjprTCSuTlkhgIAYhjOUPQCQwn0o/QMBo+bPDT+SWU6rPg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjEc0C5YcCE9mphY/zz0IU/zq4zv5kiobJsNcgdhuZXNrJBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQD83HYpd6z9w/mm2h/OKMqvZbG2c2y/f3ux4Uvmfj746a0wkrk5ZIYCAGIYzlD0AkMJ9KP0DAaPmzw0/kllOqz6lAQIDJiABIVggpdxXAuNaZVOlkPFshNfXW15VNIIQ2piLQbRhXZJlD8MiWCCRQN/b6NlruWWuPf5bHrXU0B5Y6sHMkpP/UBBdbsNOhg==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiU1JncVFnRlhxYkJibEI4NXlYbGQ5USIsIm9yaWdpbiI6Imh0dHBzOi8vbHljaGVlLnRlc3QiLCJjcm9zc09yaWdpbiI6ZmFsc2V9',
			],
			'type' => 'public-key',
		]);
		$this->assertStatus($response, 422); // Challenge is expired

		$responseList = $this->postJson('/api/WebAuthn::list');
		$this->assertOk($responseList); // code 200

		// check that the key has not been added to the list
		$responseList->assertJsonMissing([
			0 => ['id' => 'Pzcdil3rP3D-abaH84oyq9lsbZzbL9_e7HhS-Z-PvjprTCSuTlkhgIAYhjOUPQCQwn0o_QMBo-bPDT-SWU6rPg'],
		]);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthLogin(): void
	{
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

		// Generate a challenge for user_id = 1
		$response = $this->postJson('/api/WebAuthn::login/options', ['user_id' => 1]);
		$this->assertOk($response);
		$response->assertJson([
			'timeout' => 60000,
			'allowCredentials' => [
				0 => [
					'id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA',
					'type' => 'public-key',
				],
			],
		]);

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

		$responseDelete = $this->postJson('/api/WebAuthn::delete', ['id' => '_Xlz-khgFhDdkvOWyy_YqC54ExkYyp1o6HAQiybqLST-9RGBndpgI06TQygIYI7ZL2dayCMYm6J1-bXyl72obA']);
		$this->assertNoContent($responseDelete);

		Auth::logout();
		Session::flush();
	}
}
