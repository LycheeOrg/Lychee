<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\WebAuthn;

use App\Exceptions\WebAuthnVerificationException;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\EdDSA\EdDSA;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithms;
use function Safe\json_encode;
use function Safe\parse_url;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AppleAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;

/**
 * Single place where `web-auth/webauthn-lib` is configured (Feature 107, ADR-107-01).
 *
 * - Relying party: `webauthn.relying_party.id`, else the host of `APP_URL` (FR-107-06).
 * - Algorithms: ES256, RS256 and, with ext-sodium, EdDSA, as Laragear offered (FR-107-01).
 * - Attestation formats: those the library verifies without extra packages (FR-107-03).
 *   Lychee asks for `attestation: none`, so no trust path is checked (N-107-02).
 * - Origin (FR-107-06): HTTPS on the relying party id or one of its subdomains, as
 *   Laragear checked it. On `localhost` the origin must be the one of `APP_URL`
 *   (scheme, host, port), which lets local installs work over plain HTTP.
 */
final class WebAuthnFactory
{
	/** Relying party id browsers treat as a secure context without HTTPS. */
	private const LOCALHOST = 'localhost';

	private ?AttestationStatementSupportManager $attestation_statement_support_manager = null;
	private ?SerializerInterface $serializer = null;

	public function relyingPartyId(): string
	{
		$configured = config('webauthn.relying_party.id');
		if (is_string($configured) && $configured !== '') {
			return $configured;
		}

		$host = parse_url(strval(config('app.url')), PHP_URL_HOST);

		return is_string($host) && $host !== '' ? $host : request()->getHost();
	}

	/**
	 * Origins compared in full (scheme, host, port) by the origin check.
	 *
	 * Empty means: HTTPS on the relying party id or one of its subdomains.
	 * Only `localhost` gets the `APP_URL` origin, so it may use plain HTTP.
	 *
	 * @return list<string>
	 */
	public function allowedOrigins(): array
	{
		if ($this->relyingPartyId() !== self::LOCALHOST) {
			return [];
		}

		$url = parse_url(strval(config('app.url')));
		$port = isset($url['port']) ? ':' . $url['port'] : '';

		return [($url['scheme'] ?? 'https') . '://' . self::LOCALHOST . $port];
	}

	public function relyingPartyName(): string
	{
		return strval(config('webauthn.relying_party.name', 'Lychee'));
	}

	public function timeoutMilliseconds(): int
	{
		return intval(config('webauthn.challenge.timeout', 60)) * 1000;
	}

	/**
	 * Random bytes of a challenge, never fewer than the 16 WebAuthn asks for.
	 */
	public function challengeBytes(): int
	{
		return max(16, intval(config('webauthn.challenge.bytes', 32)));
	}

	/**
	 * COSE algorithm identifiers offered at registration, in order of preference.
	 *
	 * @return list<int>
	 */
	public function algorithms(): array
	{
		$algorithms = [Algorithms::COSE_ALGORITHM_ES256, Algorithms::COSE_ALGORITHM_RS256];
		if (self::supportsEdDsa()) {
			$algorithms[] = Algorithms::COSE_ALGORITHM_EDDSA;
		}

		return $algorithms;
	}

	public function attestationStatementSupportManager(): AttestationStatementSupportManager
	{
		if ($this->attestation_statement_support_manager === null) {
			$manager = AttestationStatementSupportManager::create();
			$manager->add(NoneAttestationStatementSupport::create());
			$manager->add(FidoU2FAttestationStatementSupport::create());
			$manager->add(PackedAttestationStatementSupport::create($this->algorithmManager()));
			$manager->add(TPMAttestationStatementSupport::create());
			$manager->add(AndroidKeyAttestationStatementSupport::create());
			$manager->add(AppleAttestationStatementSupport::create());
			$this->attestation_statement_support_manager = $manager;
		}

		return $this->attestation_statement_support_manager;
	}

	public function attestationValidator(): AuthenticatorAttestationResponseValidator
	{
		return AuthenticatorAttestationResponseValidator::create(
			ceremonyStepManager: $this->ceremonyStepManagerFactory()->creationCeremony()
		);
	}

	public function assertionValidator(): AuthenticatorAssertionResponseValidator
	{
		return AuthenticatorAssertionResponseValidator::create(
			ceremonyStepManager: $this->ceremonyStepManagerFactory()->requestCeremony()
		);
	}

	/**
	 * Serialise a WebAuthn object (options, credential record) to JSON.
	 */
	public function toJson(object $data): string
	{
		return $this->serializer()->serialize($data, 'json');
	}

	/**
	 * Serialise WebAuthn options to the array the browser receives, without `null` members.
	 *
	 * @return array<array-key,mixed>
	 */
	public function toBrowserArray(object $data): array
	{
		$serializer = $this->serializer();
		assert($serializer instanceof NormalizerInterface);

		/** @var array<array-key,mixed> */
		return $serializer->normalize($data, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);
	}

	/**
	 * Deserialise JSON into a WebAuthn object.
	 *
	 * @template T of object
	 *
	 * @param class-string<T> $class
	 *
	 * @return T
	 */
	public function fromJson(string $json, string $class): object
	{
		/** @var T */
		return $this->serializer()->deserialize($json, $class, 'json');
	}

	/**
	 * The browser's `PublicKeyCredential` (JSON form, FR-107-10) as a library object.
	 *
	 * @param array<string,mixed> $payload
	 *
	 * @throws WebAuthnVerificationException when the payload is not a credential
	 */
	public function parseCredential(array $payload): PublicKeyCredential
	{
		try {
			return $this->fromJson(json_encode($payload), PublicKeyCredential::class);
		} catch (\Throwable $e) {
			throw new WebAuthnVerificationException('Invalid WebAuthn response: ' . $e->getMessage(), $e);
		}
	}

	private function serializer(): SerializerInterface
	{
		if ($this->serializer === null) {
			$this->serializer = (new WebauthnSerializerFactory($this->attestationStatementSupportManager()))->create();
		}

		return $this->serializer;
	}

	private function algorithmManager(): Manager
	{
		$manager = Manager::create()->add(ES256::create(), RS256::create());
		if (self::supportsEdDsa()) {
			$manager->add(new EdDSA());
		}

		return $manager;
	}

	private function ceremonyStepManagerFactory(): CeremonyStepManagerFactory
	{
		$factory = new CeremonyStepManagerFactory();
		$factory->setAllowedOrigins($this->allowedOrigins(), allowSubdomains: true);
		$factory->setAlgorithmManager($this->algorithmManager());
		$factory->setAttestationStatementSupportManager($this->attestationStatementSupportManager());

		return $factory;
	}

	private static function supportsEdDsa(): bool
	{
		return extension_loaded('sodium');
	}
}
