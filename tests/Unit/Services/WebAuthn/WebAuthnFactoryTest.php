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

namespace Tests\Unit\Services\WebAuthn;

use App\Services\WebAuthn\WebAuthnFactory;
use function Safe\json_encode;
use Tests\AbstractTestCase;
use Tests\Support\WebAuthn\SoftAuthenticator;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Feature 107: FR-107-01, FR-107-03, FR-107-06.
 */
class WebAuthnFactoryTest extends AbstractTestCase
{
	public function testRelyingPartyIdComesFromWebAuthnConfig(): void
	{
		config(['webauthn.relying_party.id' => 'photos.example.org', 'app.url' => 'https://other.example.org']);

		self::assertSame('photos.example.org', resolve(WebAuthnFactory::class)->relyingPartyId());
	}

	public function testRelyingPartyIdFallsBackToAppUrlHost(): void
	{
		config(['webauthn.relying_party.id' => null, 'app.url' => 'https://gallery.example.org/lychee']);

		self::assertSame('gallery.example.org', resolve(WebAuthnFactory::class)->relyingPartyId());
	}

	public function testRelyingPartyName(): void
	{
		config(['webauthn.relying_party.name' => 'My Photos']);

		self::assertSame('My Photos', resolve(WebAuthnFactory::class)->relyingPartyName());
	}

	public function testTimeoutIsInMilliseconds(): void
	{
		config(['webauthn.challenge.timeout' => 60]);

		self::assertSame(60000, resolve(WebAuthnFactory::class)->timeoutMilliseconds());
	}

	public function testAlgorithmsMatchLaragear(): void
	{
		$expected = extension_loaded('sodium') ? [-7, -257, -8] : [-7, -257];

		self::assertSame($expected, resolve(WebAuthnFactory::class)->algorithms());
	}

	public function testAcceptedAttestationFormats(): void
	{
		$manager = resolve(WebAuthnFactory::class)->attestationStatementSupportManager();

		foreach (['none', 'fido-u2f', 'packed', 'tpm', 'android-key', 'apple'] as $format) {
			self::assertTrue($manager->has($format), $format . ' must be accepted');
		}
		self::assertFalse($manager->has('android-safetynet'));
	}

	public function testAllowedOriginsAreEmptyOutsideLocalhost(): void
	{
		config(['webauthn.relying_party.id' => 'example.org', 'app.url' => 'http://photos.example.org:8080']);

		self::assertSame([], resolve(WebAuthnFactory::class)->allowedOrigins());
	}

	public function testAllowedOriginOnLocalhostIsAppUrlOrigin(): void
	{
		config(['webauthn.relying_party.id' => null, 'app.url' => 'http://localhost:8123/gallery']);

		self::assertSame(['http://localhost:8123'], resolve(WebAuthnFactory::class)->allowedOrigins());
	}

	/**
	 * FR-107-06: HTTPS origin on the relying party id or one of its subdomains;
	 * on localhost exactly the `APP_URL` origin, which may be plain HTTP.
	 *
	 * @return array<string,array{string,string,string,string|null}>
	 */
	public static function originProvider(): array
	{
		return [
			'https on the relying party' => ['https://example.org', 'example.org', 'https://example.org', null],
			'https on a subdomain' => ['https://photos.example.org', 'example.org', 'https://photos.example.org', null],
			'https although APP_URL says http and another port' => ['https://example.org', 'example.org', 'http://example.org:8080', null],
			'http elsewhere' => ['http://example.org', 'example.org', 'http://example.org', 'Invalid scheme. HTTPS required.'],
			'other domain' => ['https://evil-example.org', 'example.org', 'https://example.org', 'Invalid origin.'],
			'parent domain' => ['https://example.org', 'photos.example.org', 'https://photos.example.org', 'Invalid origin.'],
			'https on localhost' => ['https://localhost', 'localhost', 'https://localhost', null],
			'http on localhost' => ['http://localhost:8123', 'localhost', 'http://localhost:8123', null],
			'localhost on another port' => ['http://localhost:9000', 'localhost', 'http://localhost:8123', 'Invalid origin. Not in the list of allowed origins.'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('originProvider')]
	public function testOriginRule(string $origin, string $rp_id, string $app_url, ?string $rejection): void
	{
		config(['webauthn.relying_party.id' => $rp_id, 'app.url' => $app_url]);
		$factory = resolve(WebAuthnFactory::class);
		$authenticator = new SoftAuthenticator();
		$options = PublicKeyCredentialRequestOptions::create(challenge: random_bytes(32), rpId: $rp_id, userVerification: 'preferred');
		$credential = $factory->fromJson(
			json_encode($authenticator->assertion($options->challenge, origin: $origin, rp_id: $rp_id)),
			PublicKeyCredential::class);
		$response = $credential->response;
		self::assertInstanceOf(AuthenticatorAssertionResponse::class, $response);

		if ($rejection !== null) {
			$this->expectException(AuthenticatorResponseVerificationException::class);
			$this->expectExceptionMessage($rejection);
		}
		$record = $factory->assertionValidator()->check($authenticator->credentialRecord(), $response, $options, $rp_id, $authenticator->user_handle);

		self::assertSame(1, $record->counter);
	}
}
