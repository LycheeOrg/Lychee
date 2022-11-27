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
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Tests\Feature\Traits\CatchFailures;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
	use CatchFailures;

	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testWebAuthTest(): void
	{
		Auth::loginUsingId(1);

		$response = $this->postJson('/api/WebAuthn::register/options');
		$this->assertOk($response);

		$response = $this->postJson('/api/WebAuthn::register', [
			'id' => '-PhslGzltOv3nJ0j8Or1AuNHh9kgmMQmOdM0A7eF7yJcAuSZzFa9YhSHfrYvyllhNUhuIMTE6hFYA3Ef7gCOwg',
			'rawId' => '+PhslGzltOv3nJ0j8Or1AuNHh9kgmMQmOdM0A7eF7yJcAuSZzFa9YhSHfrYvyllhNUhuIMTE6hFYA3Ef7gCOwg==',
			'response' => [
				'attestationObject' => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjEc0C5YcCE9mphY/zz0IU/zq4zv5kiobJsNcgdhuZXNrJBAAAAAAAAAAAAAAAAAAAAAAAAAAAAQPj4bJRs5bTr95ydI/Dq9QLjR4fZIJjEJjnTNAO3he8iXALkmcxWvWIUh362L8pZYTVIbiDExOoRWANxH+4AjsKlAQIDJiABIVggyctxuQjqpB1PlDdNSwyb0kR0eKna/QyhZLB8LuC9sTkiWCA8wbkGKQSgde657MkSh4bq1zq8BJAqLL/UG6RcuBVxpA==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiQl9vclJ0UVRQUEpVT0JyMjlPRGw1dyIsIm9yaWdpbiI6Imh0dHBzOi8vbHljaGVlLnRlc3QiLCJjcm9zc09yaWdpbiI6ZmFsc2V9',
			],
			'type' => 'public-key',
		]);
		$response->assertStatus(422);

		Auth::logout();
		Session::flush();

		$response = $this->postJson('/api/WebAuthn::login/options', ['user_id' => 0]);
		$this->assertOk($response);

		$response = $this->postJson('/api/WebAuthn::login', [
			'id' => 'jQJF5u0Fn-MsdabIxKJoxc19XSLXDCSDqs4g8TV1rXXXBDSEoT6LeRN60CfxZskRxq15EEl43OIbPluD7dVT0A',
			'rawId' => 'jQJF5u0Fn+MsdabIxKJoxc19XSLXDCSDqs4g8TV1rXXXBDSEoT6LeRN60CfxZskRxq15EEl43OIbPluD7dVT0A==',
			'response' => [
				'authenticatorData' => 'c0C5YcCE9mphY/zz0IU/zq4zv5kiobJsNcgdhuZXNrIBAAAADQ==',
				'clientDataJSON' => 'eyJ0eXBlIjoid2ViYXV0aG4uZ2V0IiwiY2hhbGxlbmdlIjoiOHhfMm5CcnNVb1dkcVFtTkN3VFY2ZyIsIm9yaWdpbiI6Imh0dHBzOi8vbHljaGVlLnRlc3QiLCJjcm9zc09yaWdpbiI6ZmFsc2V9',
				'signature' => 'MEYCIQCjN9LZ1IrcycOkSRsxswKnh0gcOu1ZpexcvhxyhUgqQwIhAJuDDQnKqZD0+ed1EA5If+Sowv2T9tBodVb4a+DReTQZ',
				'userHandle' => '',
			],
			'type' => 'public-key',
		]);
		$response->assertStatus(422);

		$key = (new WebAuthnCredential())->forceFill([
			'id' => 'hyxPTjCUCWYPcVTxFy7WjCXATwU7UDLI9nPGqifqs9ohskBuVih4Nzdp3UAl-wHTda4CUoAE_ylfQveayx07ug',
			'authenticatable_id' => 0,
			'user_id' => '9058e01e7d84466595fd3ae7b8ad89a3',
			'alias' => '',
			'counter' => '0',
			'rp_id' => 'https://localhost',
			'origin' => 'https://localhost',
			'transports' => '[]',
			'aaguid' => '00000000-0000-0000-0000-000000000000',
			'attestation_format' => 'none',
			'public_key' => '',
		]);
		$user = User::query()->find(1);
		$user->webAuthnCredentials()->save($key);

		Auth::loginUsingId(1);

		$response = $this->postJson('/api/WebAuthn::list');
		$this->assertOk($response); // code 200 something

		$response = $this->postJson('/api/WebAuthn::delete', ['id' => 'hyxPTjCUCWYPcVTxFy7WjCXATwU7UDLI9nPGqifqs9ohskBuVih4Nzdp3UAl-wHTda4CUoAE_ylfQveayx07ug']);
		$this->assertNoContent($response);

		Auth::logout();
		Session::flush();

		$response = $this->postJson('/api/WebAuthn::delete', ['id' => 'hyxPTjCUCWYPcVTxFy7WjCXATwU7UDLI9nPGqifqs9ohskBuVih4Nzdp3UAl-wHTda4CUoAE_ylfQveayx07ug']);
		$this->assertUnauthorized($response);
	}
}
