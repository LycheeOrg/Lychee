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

namespace Tests\Unit\Config;

use App\Enum\MapProviders;
use Bepsvpt\SecureHeaders\SecureHeaders;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * The security headers only carry what Lychee needs: directives that differ
 * from the browser defaults, and the sources of the webshop providers that
 * are configured. Every response carries them, so every byte counts against
 * the reverse proxy's header buffer (discussion #4901).
 */
class SecureHeadersConfigTest extends AbstractTestCase
{
	private const VARIABLES = ['PAYPAL_CLIENT_ID', 'MOLLIE_API_KEY', 'STRIPE_API_KEY'];

	/** @var array<string,array{server:mixed,env:mixed,putenv:string|false}> */
	private array $saved = [];

	protected function setUp(): void
	{
		parent::setUp();
		foreach (self::VARIABLES as $name) {
			$this->saved[$name] = ['server' => $_SERVER[$name] ?? null, 'env' => $_ENV[$name] ?? null, 'putenv' => getenv($name)];
			$this->setVariable($name, null);
		}
	}

	protected function tearDown(): void
	{
		foreach ($this->saved as $name => $value) {
			$this->setVariable($name, $value['putenv'] === false ? null : $value['putenv']);
			if ($value['server'] !== null) {
				$_SERVER[$name] = $value['server'];
			}
			if ($value['env'] !== null) {
				$_ENV[$name] = $value['env'];
			}
		}
		parent::tearDown();
	}

	public function testObsoleteAndDefaultValuedHeadersAreNotSent(): void
	{
		$headers = $this->headers();

		self::assertArrayNotHasKey('X-XSS-Protection', $headers);
		self::assertArrayNotHasKey('X-Download-Options', $headers);
		self::assertArrayNotHasKey('Cross-Origin-Embedder-Policy', $headers);
		self::assertArrayNotHasKey('Cross-Origin-Opener-Policy', $headers);
		self::assertSame('nosniff', $headers['X-Content-Type-Options']);
		self::assertSame('no-referrer', $headers['Referrer-Policy']);
		self::assertSame('cross-origin', $headers['Cross-Origin-Resource-Policy']);
	}

	public function testPermissionsPolicyOnlyListsRestrictedFeatures(): void
	{
		self::assertSame('geolocation=(), midi=()', $this->headers()['Permissions-Policy']);
	}

	public function testCspCarriesNoUnusedSources(): void
	{
		$csp = $this->headers()['Content-Security-Policy'];

		self::assertStringNotContainsString('update.json', $csp);
		self::assertStringNotContainsString('paypal', $csp);
		self::assertStringNotContainsString('mollie', $csp);
		self::assertStringNotContainsString('stripe', $csp);
		self::assertStringNotContainsString("'report-sample'", $csp);
		self::assertStringNotContainsString("'unsafe-hashes'", $csp);
		self::assertNotContains("'unsafe-inline'", self::sources($csp, 'script-src'));
	}

	public function testCspAllowsEveryMapTileHost(): void
	{
		$img_src = self::sources($this->headers()['Content-Security-Policy'], 'img-src');

		foreach (MapProviders::cases() as $provider) {
			// https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png => https://*.tile.openstreetmap.fr/osmfr/
			$source = str_replace('{s}.', '*.', strstr($provider->getLayer(), '{z}', true));
			self::assertContains($source, $img_src, $provider->value . ' tiles are blocked by the CSP.');
		}
	}

	public function testScriptHashesMatchTheInlineScripts(): void
	{
		$expected = [
			self::inlineScriptHash('resources/views/vueapp.blade.php'),
			self::inlineScriptHash('resources/views/update/error.blade.php'),
		];

		self::assertEqualsCanonicalizing($expected, $this->config()['csp']['script-src']['hashes']['sha256']);
	}

	/**
	 * @return array<string,array{string,array<int,array{string,string}>}>
	 */
	public static function webshopProvider(): array
	{
		return [
			'PayPal' => ['PAYPAL_CLIENT_ID', [
				['connect-src', 'https://www.paypal.com'],
				['connect-src', 'https://www.sandbox.paypal.com'],
				['img-src', 'https://www.paypalobjects.com'],
				['script-src', 'https://www.paypal.com/sdk/js'],
				['script-src', "'unsafe-inline'"],
			]],
			'Mollie' => ['MOLLIE_API_KEY', [['script-src', 'https://js.mollie.com']]],
			'Stripe' => ['STRIPE_API_KEY', [['script-src', 'https://js.stripe.com']]],
		];
	}

	/**
	 * @param array<int,array{string,string}> $sources
	 */
	#[DataProvider('webshopProvider')]
	public function testWebshopSourcesAreAllowedOnceTheProviderIsConfigured(string $variable, array $sources): void
	{
		$this->setVariable($variable, 'configured');
		$csp = $this->headers()['Content-Security-Policy'];

		foreach ($sources as [$directive, $source]) {
			self::assertContains($source, self::sources($csp, $directive));
		}
	}

	public function testPayPalDropsTheScriptHashes(): void
	{
		// Browsers ignore 'unsafe-inline' as soon as a hash is listed.
		$this->setVariable('PAYPAL_CLIENT_ID', 'configured');

		self::assertSame([], $this->config()['csp']['script-src']['hashes']['sha256']);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function config(): array
	{
		return require base_path('config/secure-headers.php');
	}

	/**
	 * @return array<string,string>
	 */
	private function headers(): array
	{
		return (new SecureHeaders($this->config()))->headers();
	}

	/**
	 * @return string[]
	 */
	private static function sources(string $csp, string $directive): array
	{
		foreach (explode('; ', $csp) as $entry) {
			$parts = explode(' ', $entry);
			if ($parts[0] === $directive) {
				return array_slice($parts, 1);
			}
		}

		self::fail($directive . ' is missing from ' . $csp);
	}

	private static function inlineScriptHash(string $view): string
	{
		self::assertSame(1, preg_match('#<script>(.*?)</script>#s', file_get_contents(base_path($view)), $matches), 'No inline script in ' . $view);

		return base64_encode(hash('sha256', $matches[1], true));
	}

	private function setVariable(string $name, ?string $value): void
	{
		if ($value === null) {
			unset($_SERVER[$name], $_ENV[$name]);
			putenv($name);

			return;
		}

		$_SERVER[$name] = $value;
		$_ENV[$name] = $value;
		putenv($name . '=' . $value);
	}
}
